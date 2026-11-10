# Plan: Server-Side Tracking for Yireo_GoogleTagManager2

## Current state
- `settings/serverside_enabled` + `settings/serverside_gtm_url` only change the GTM loader URL (`Config::getGoogleTagmanagerUrl()`) and enable an order-state filter in `DataLayer\Event\Purchase`. No server-to-server sending exists.
- `DataLayer\Event\*` classes (Purchase, Refund, AddToCart, BeginCheckout, ...) already build GA4 ecommerce arrays: reuse them.
- Consent is only stored in `localStorage` (`yireoGoogleTagManager2ConsentModes`), not readable server-side.

## Decisions (confirmed by user)
| Topic | Decision |
|---|---|
| Destination | GA4 Measurement Protocol payload, endpoint configurable: direct Google (`https://www.google-analytics.com/mp/collect`) or custom sGTM URL (`<url>/mp/collect`) |
| Events | purchase, refund, add_to_cart, remove_from_cart, begin_checkout, add_shipping_info, add_payment_info, login, sign_up, add_to_wishlist, each toggleable via multiselect |
| Purchase trigger | Backend setting: on order placement / on reaching configured order states (reuse `order_states_for_purchase_event`) / on invoice paid |
| Dispatch | Backend setting: cron or message queue (`db` connection); both backed by one outbox table for dedup/retries/audit |
| Browser purchase | Setting "Suppress browser purchase event", default Yes (only when server-side purchase is active) |
| Location | Inside Yireo_GoogleTagManager2, namespace `Yireo\GoogleTagManager2\ServerSide\` |
| Legacy settings | Keep `settings/serverside_*` for BC (relabel as "Custom GTM container URL") |

## Steps
1. Config: new group `serverside_tracking` (enabled, destination, measurement_id, api_secret (encrypted), sgtm_url, events, purchase_trigger, dispatch_mode, suppress_browser_purchase, require_consent, validation_mode, max_attempts, retention_days) + defaults in `etc/config.xml`, `ServerSide\Config`, source models.
2. `etc/db_schema.xml` + whitelist: `yireo_gtm_serverside_context` (quote_id, order_id, client_id, session_id, user_id, consent JSON, created_at) and `yireo_gtm_serverside_event` outbox (entity_id, store_id, event_name, dedup_key unique, payload JSON, status, attempts, last_error, created_at, sent_at).
3. Context capture: `CookieReader` parses `_ga`, `_ga_<suffix>`, new `yireo_gtm2_consent` cookie; fallback client_id. Observer on `sales_model_service_quote_submit_success` stores context. JS: `yireoGoogleTagManager2SetConsent()` also writes the consent cookie.
4. Payload: `MeasurementProtocolMapper` maps dataLayer event arrays to MP format; `CreditmemoRefund` builder for partial refunds.
5. Dispatch: `EventPublisher` (config/event/consent check, map, outbox insert w/ dedup, dispatcher). `QueueDispatcher` (topic `yireo_gtm2.serverside.event`, communication/queue xml, consumer). `CronDispatcher` (no-op, cron processes). `Sender` via Curl with short timeouts, logs via Debugger. Cron: process_pending, retry_failed, cleanup.
6. Triggers: purchase (place_after / order save_after states / invoice pay), refund (creditmemo save_after), cart/checkout/customer events next to existing triggers; browser purchase suppression.
7. CLI: `yireo_googletagmanager2:serverside:test`, `yireo_googletagmanager2:serverside:process`.
8. Tests: unit (cookie parsing, mapper, dedup, dispatcher selection), integration (context, purchase per trigger, refund, dedup, cron with stub HTTP, queue publish, suppression), Playwright (consent cookie, suppression). phpstan + extension checker.
9. Docs: `SERVERSIDE.md`, README/ARCHITECTURE pointers, MEMORY.md, conventional commits.

## Assumptions
- `require_consent` default Yes when consent banner enabled; skip hits lacking analytics consent.
- No IP / user agent forwarding.
- No admin grid in v1.
