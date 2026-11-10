# Breakdown: Server-Side Tracking

Module root: `/data/git/yireo/magento2-extensions/Yireo_GoogleTagManager2`. Namespace for new code: `Yireo\GoogleTagManager2\ServerSide\` (folder `ServerSide/`).

## Unit A: Config + schema + core (magento2-backend-developer)
- `etc/adminhtml/system.xml`: relabel `settings/serverside_enabled` to "Custom GTM container URL" (comment: only changes the GTM loader URL / purchase order state filter; for real server-to-server tracking use group "Server-Side Tracking"), keep field ids.
- New group `serverside_tracking` (sortOrder 2, label "Server-Side Tracking") fields, paths `googletagmanager2/serverside_tracking/<id>`:
  - `enabled` (Yesno, default 0)
  - `destination` (source `ServerSide\Config\Source\Destination`: `ga4` = Google GA4 Measurement Protocol, `sgtm` = Server-side GTM container; default `ga4`)
  - `measurement_id` (text, `G-XXXX`)
  - `api_secret` (obscure, backend `Magento\Config\Model\Config\Backend\Encrypted`)
  - `sgtm_url` (text, depends destination=sgtm; endpoint = rtrim(url,'/') . '/mp/collect')
  - `events` (multiselect, source `ServerSide\Config\Source\Events`; default all 10: purchase,refund,add_to_cart,remove_from_cart,begin_checkout,add_shipping_info,add_payment_info,login,sign_up,add_to_wishlist)
  - `purchase_trigger` (source `ServerSide\Config\Source\PurchaseTrigger`: `order_place`, `order_state`, `invoice_pay`; default `order_place`). `order_state` reuses `settings/order_states_for_purchase_event` (Config::getOrderStatesForPurchaseEvent()).
  - `dispatch_mode` (source `ServerSide\Config\Source\DispatchMode`: `cron`, `queue`; default `cron`)
  - `suppress_browser_purchase` (Yesno, default 1)
  - `require_consent` (Yesno, default 1; only effective when `consent/enabled`=1)
  - `validation_mode` (Yesno, default 0: send to `/debug/mp/collect` and log validation messages)
  - `max_attempts` (default 5), `retention_days` (default 30)
- Defaults in `etc/config.xml` (api_secret `<api_secret backend_model="Magento\Config\Model\Config\Backend\Encrypted"/>`).
- `ServerSide/Config.php`: typed getters per store id (`isEnabled(?int $storeId)`, `getDestination`, `getMeasurementId`, `getApiSecret` (decrypt via EncryptorInterface), `getEndpointUrl(?int $storeId)`, `isEventEnabled(string $event, ?int $storeId)`, `getPurchaseTrigger`, `getDispatchMode`, `suppressBrowserPurchase` (true only if enabled && purchase event enabled && setting), `requireConsent` (true only when consent enabled), `isValidationMode`, `getMaxAttempts`, `getRetentionDays`). `isEnabled` requires measurement_id + api_secret.
- `etc/db_schema.xml` + `etc/db_schema_whitelist.json` (generate via `bin/magento setup:db-declaration:generate-whitelist --module-name=Yireo_GoogleTagManager2`):
  - `yireo_gtm_serverside_context`: entity_id PK, quote_id (int unsigned, unique), order_id (int unsigned nullable, index), client_id varchar(64), session_id varchar(64) nullable, user_id varchar(64) nullable, consent text nullable (JSON), created_at timestamp default CURRENT_TIMESTAMP.
  - `yireo_gtm_serverside_event`: entity_id PK, store_id smallint unsigned, event_name varchar(64), dedup_key varchar(191) unique, payload mediumtext (JSON of full MP body), status varchar(16) (`pending|processing|sent|failed`), attempts smallint unsigned default 0, last_error text nullable, created_at timestamp, updated_at timestamp on_update, sent_at timestamp nullable. Indexes on (status, attempts), created_at.
- Persistence without heavy repository boilerplate: `ServerSide/Context/ContextRepository.php` and `ServerSide/Outbox/OutboxRepository.php` using `ResourceConnection` directly (insertOnDuplicate/insert ignore for dedup; `insert(...)` returns bool/created id; `getPending(int $limit)`, `getRetryable(int $maxAttempts, int $limit)`, `markSent`, `markFailed`, `claim` (status pending->processing atomically via update with where), `cleanup(int $days)`, `getById`).
- `ServerSide/Context/CookieReader.php`: parse `_ga` (`GA1.1.123.456` → `123.456`), `_ga_<MEASUREMENT_SUFFIX>` (`GS1.1.<session_id>.…` and new `GS2.1.s<session_id>$o…` format) → session_id, cookie `yireo_gtm2_consent` (JSON `{"analytics":bool,"marketing":bool,"preferences":bool}`, urlencoded). Fallback client_id `random_int.time()` style (`<10 digits>.<unix ts>`).
- `ServerSide/Context/ContextCollector.php`: builds context array from cookies + customer session (user_id = customer id when logged in).
- `ServerSide/Payload/MeasurementProtocolMapper.php`: input dataLayer event array `['event'=>name,'ecommerce'=>[...]]` (+ optional top-level keys) and a Context value object → MP body `{client_id, user_id?, timestamp_micros?, consent?: {ad_user_data, ad_personalization}, events:[{name, params:{...ecommerce, session_id?, engagement_time_msec: 1}}]}`. Strip non-GA4 keys (`value_adjusted`, `value_adjusted_currency`, `payment_method` kept as param is fine?) – keep only scalar params + `items` (items: keep known GA4 item keys, cast price/quantity numeric). Ensure `value` float, `currency` string.
- `ServerSide/Payload/CreditmemoRefund.php`: builds refund event array from a creditmemo (transaction_id = order increment id, value = creditmemo grand total, currency, tax, shipping, items from creditmemo items with qty > 0 (item_id = sku)).
- `ServerSide/Dispatch/DispatcherInterface.php` (`dispatch(int $outboxId): void`), `CronDispatcher` (no-op), `QueueDispatcher` (publish outbox id to topic `yireo_gtm2.serverside.event` via `Magento\Framework\MessageQueue\PublisherInterface`), `DispatcherResolver` picks by config.
- `etc/communication.xml`, `etc/queue_publisher.xml`, `etc/queue_topology.xml`, `etc/queue_consumer.xml` (connection `db`, consumer `yireo_gtm2.serverside.event`, handler `ServerSide\Queue\Consumer::process(string $outboxId)`; topic request type `string`).
- `ServerSide/Sender.php`: `send(array $outboxRow): bool` POST JSON via `Magento\Framework\HTTP\Client\Curl` (timeout 3s) to endpoint with `?measurement_id=..&api_secret=..`; 2xx = success; validation mode → debug endpoint and log validationMessages; log via `Logger\Debugger` (never log api_secret).
- `ServerSide/Processor.php`: `processById(int $id)`, `processPending(int $limit = 100)`, `retryFailed(int $limit = 100)`: claim → send → mark sent/failed (attempts++).
- `ServerSide/EventPublisher.php`: `publish(string $eventName, array $dataLayerEvent, int $storeId, string $dedupKey, ?Context $context)`: check enabled + event enabled + consent (if requireConsent and context consent analytics !== true → skip, debug log) → map → outbox insert (skip if duplicate) → dispatcher. Must never throw to callers (catch Throwable, log).
- Cron `etc/crontab.xml` (group default): `yireo_gtm2_serverside_process` every minute (Processor::processPending), `yireo_gtm2_serverside_retry` every 15 min, `yireo_gtm2_serverside_cleanup` daily.
- CLI `etc/di.xml` commandList: `yireo_googletagmanager2:serverside:test` (options `--store`, sends a test `page_view`-like event "yireo_gtm2_test" directly via Sender in validation mode unless `--live`; prints response) and `yireo_googletagmanager2:serverside:process` (processes pending + retry, `--limit`).
- module.xml sequence + composer.json: add `Magento_Cron` (magento/module-cron), `Magento_MessageQueue` (magento/module-message-queue) and `magento/framework-message-queue`; keep existing ordering style (alphabetical).

## Unit B: Triggers, context capture, suppression, JS (magento2-backend-developer)
- Global `etc/events.xml` (all areas incl. adminhtml/cron/webapi):
  - `sales_model_service_quote_submit_success` → `ServerSide\Observer\StoreContext` (store context row with quote_id + order_id; only in frontend/webapi_rest areas when cookies exist; harmless otherwise) and when trigger=`order_place` publish purchase (dedup `purchase:<increment_id>`).
  - `sales_order_save_after` → `ServerSide\Observer\PublishPurchaseOnOrderState` when trigger=`order_state` and state in configured states.
  - `sales_order_invoice_pay` → `ServerSide\Observer\PublishPurchaseOnInvoicePay` when trigger=`invoice_pay`.
  - `sales_order_creditmemo_save_after` → `ServerSide\Observer\PublishRefund` (dedup `refund:<creditmemo increment_id or entity_id>`).
  Purchase builder: use `DataLayer\Event\Purchase` but bypass its `hasServerSideTracking()` state filter (e.g. create via factory and call setOrder; if the legacy filter returns [], build anyway — add a small method or separate builder in ServerSide so legacy behaviour is unchanged). Context for admin/cron triggers is loaded from `yireo_gtm_serverside_context` by order_id/quote_id; fallback generated client_id.
- Frontend events (`etc/frontend/events.xml` / plugins, next to existing ones): add_to_cart, remove_from_cart, login, sign_up, add_to_wishlist → separate `ServerSide\Observer\*` observers reusing the `DataLayer\Event\*` builders, context from current request cookies (dedup key `<event>:<uuid>`). begin_checkout: on `controller_action_predispatch_checkout_index_index` (dedup `begin_checkout:<quote_id>`). add_shipping_info / add_payment_info: plugins next to existing ones (after save shipping info / payment info) with dedup `<event>:<quote_id>:<hash of method>`.
- Browser purchase suppression when `ServerSide\Config::suppressBrowserPurchase()`: `Observer\TriggerPurchaseDataLayerEvent` skips adding `purchase_event`; `DataLayer\Processor\SuccessPage` removes the `purchase` event from the data layer (inspect how events end up in `$data` and remove properly).
- JS: `view/frontend/templates/script-consent-default.phtml` `yireoGoogleTagManager2SetConsent()` also writes cookie `yireo_gtm2_consent` (path=/, max-age 1 year, SameSite=Lax, value encodeURIComponent(JSON of analytics/marketing/preferences)); also write it on load if localStorage has values but cookie is missing.

## Unit C: Tests (magento2-unit-test-creator, magento2-integration-test-creator, magento2-playwright-test-creator)
- Unit: CookieReader, MeasurementProtocolMapper, ServerSide\Config, DispatcherResolver, EventPublisher (consent skip, duplicate skip), Sender (endpoint building, success/failure).
- Integration: schema exists; context stored on order place; purchase per trigger (order_place, order_state, invoice_pay); refund on creditmemo; dedup; Processor with stubbed Sender/Curl; queue publish (QueueDispatcher puts message in `queue_message`); suppression in TriggerPurchaseDataLayerEvent + success page.
- Playwright: consent cookie written by `yireoGoogleTagManager2SetConsent()`; success page has no purchase event when suppression on (if feasible).

## Unit D: Docs
- `SERVERSIDE.md`, pointers in README.md and ARCHITECTURE.md, MEMORY.md entries.
