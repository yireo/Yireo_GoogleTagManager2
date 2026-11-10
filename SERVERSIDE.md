# Server-Side Tracking

Next to pushing events to the `dataLayer` in the browser, this extension is able to send ecommerce events
server-to-server, using the [GA4 Measurement Protocol](https://developers.google.com/analytics/devguides/collection/protocol/ga4).
Server-side events are not blocked by ad blockers and are sent even if the customer never reaches the success page
(for instance with offsite payments).

## Configuration
Navigate to **Stores > Configuration > Yireo > Yireo GoogleTagManager > Server-Side Tracking**:

- **Enabled**: Enable server-side tracking. Nothing is sent unless both a Measurement ID and an API secret are configured.
- **Destination**:
    - *Google Analytics 4 (Measurement Protocol)*: Events are sent to `https://www.google-analytics.com/mp/collect`.
    - *Server-side GTM container*: Events are sent to `<Server-side GTM URL>/mp/collect`. Your sGTM container needs a
      Measurement Protocol client to handle these requests.
- **Measurement ID** (`G-XXXXXXX`) and **API secret** (GA4 Admin > Data Streams > Measurement Protocol API secrets).
  The API secret is stored encrypted.
- **Events**: The events to send server-side: `purchase`, `refund`, `add_to_cart`, `remove_from_cart`,
  `begin_checkout`, `add_shipping_info`, `add_payment_info`, `login`, `sign_up` and `add_to_wishlist`.
- **Purchase trigger**: When to send the `purchase` event:
    - *When the order is placed*
    - *When the order reaches one of the configured order states* (setting **Orders states for purchase event** in
      the group **Settings**)
    - *When an invoice is paid*
- **Suppress browser purchase event** (default *Yes*): When the `purchase` event is sent server-side, it is no longer
  pushed to the `dataLayer` in the browser, so that purchases are not counted twice.
- **Dispatch mode**:
    - *Cron*: Pending events are sent by the cron job `yireo_gtm2_serverside_process` every minute.
    - *Message queue*: Every event is published to the topic `yireo_gtm2.serverside.event` (connection `db`). Make
      sure the consumer is running: `bin/magento queue:consumers:start yireo_gtm2.serverside.event`.
- **Require analytics consent** (default *Yes*): Only applies when the consent banner of this extension is enabled.
  Events are only sent when the customer granted analytics consent.
- **Validation mode**: Send events to the validation endpoint (`/debug/mp/collect`) instead. GA4 does not record
  these events. Validation messages are logged when **Debug** is enabled.
- **Maximum attempts** and **Retention of sent events (days)**.

The older settings **Custom GTM container URL** (`settings/serverside_enabled`) and **Container URL**
(`settings/serverside_gtm_url`) only change the URL that the GTM container is loaded from in the browser. They do not
send any data server-to-server.

## How it works
- **Context**: GA4 needs the `client_id` of the browser to stitch server-side events to the browser session. The
  `client_id` is read from the `_ga` cookie, the `session_id` from the `_ga_<MEASUREMENT_ID>` cookie and the consent
  from the cookie `yireo_gtm2_consent` (written by `yireoGoogleTagManager2SetConsent()` of the consent banner). When an
  order is placed, this context is stored in the table `yireo_gtm_serverside_context`, so that events triggered later
  (in the Admin Panel, via cron or via a payment webhook) still use the original `client_id`. If no `_ga` cookie is
  found, a random `client_id` is generated. The `user_id` is the customer ID of logged-in customers. No IP address or
  user agent is forwarded.
- **Payload**: The existing `DataLayer\Event\*` classes generate the GA4 event data, which is converted into a
  Measurement Protocol payload by `ServerSide\Payload\MeasurementProtocolMapper`. A refund is based on the credit memo
  (`ServerSide\Payload\CreditmemoRefund`), so partial refunds are supported.
- **Outbox**: Every event is stored in the table `yireo_gtm_serverside_event` with a unique deduplication key (for
  instance `purchase:<increment ID>` or `refund:<credit memo ID>`), so that an order is never sent twice. The status
  of every event (`pending`, `processing`, `sent`, `failed`) and the last error are stored as well.
- **Retries**: Failed events are retried every 15 minutes by the cron job `yireo_gtm2_serverside_retry`, until the
  maximum number of attempts is reached. Sent and failed events are removed by the cron job
  `yireo_gtm2_serverside_cleanup` after the retention period.

## CLI
```bash
bin/magento yireo_googletagmanager2:serverside:test --store=1        # Send a test event to the validation endpoint
bin/magento yireo_googletagmanager2:serverside:test --store=1 --live # Send a test event to the live endpoint
bin/magento yireo_googletagmanager2:serverside:process --limit=100   # Send pending events and retry failed events
```

## Debugging
Enable **Debug** in the group **Settings** to log every request and response to `var/log/yireo_googletagmanager2.log`
(the API secret is never logged). Inspect the outbox:

```sql
SELECT entity_id, event_name, dedup_key, status, attempts, last_error FROM yireo_gtm_serverside_event ORDER BY entity_id DESC;
```
