# Magento 2 module for Google Tag Manager

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-googletagmanager2)
<!-- badges.specs.end -->


**A client-side implementation of Google Tag Manager for Magento 2**

**Also see: https://www.yireo.com/software/magento-extensions/googletagmanager2**

[![Latest Stable Version](https://poser.pugx.org/yireo/magento2-googletagmanager2/v)](//packagist.org/packages/yireo/magento2-googletagmanager2) [![Total Downloads](https://poser.pugx.org/yireo/magento2-googletagmanager2/downloads)](//packagist.org/packages/yireo/magento2-googletagmanager2)  [![License](https://poser.pugx.org/yireo/magento2-googletagmanager2/license)](//packagist.org/packages/yireo/magento2-googletagmanager2)

[![Unit Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/unit-tests.yml)
[![Static Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/static-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/static-tests.yml)
[![Integration Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/integration-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/integration-tests.yml)
[![DI Compilation](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/compile.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/compile.yml)

![Luma Themes compatible](https://img.shields.io/badge/Luma%20Themes-compatible-blue?style=flat)
![Hyvä Themes compatible](https://img.shields.io/badge/Hyv%C3%A4%20Themes-compatible-blue?style=flat)

## Requirements
Supported versions: Magento 2.3.7 or higher and 2.4.1 or higher (partially because of the requirement for PHP 7.4 or PHP 8.1).

See `composer.json` for other requirements.

## Additional modules
- [Yireo_GoogleTagManager2LokiCheckout](https://github.com/yireo/Yireo_GoogleTagManager2LokiCheckout) - integration with Loki Checkout;
- [Yireo_GoogleTagManager2HyvaCheckout](https://github.com/yireo/Yireo_GoogleTagManager2HyvaCheckout) - integration with Hyva Checkout;

## About version 3
The `Yireo_GoogleTagManager2` module was refactored heavily: The architecture was redesigned, GA4 support was being added, GA3 support (aka UA/EE) was being removed, click-events were added and overall there was a huge extensibility being added. The refactoring has come available under a new major version 3. If you were not using this
extension yet, you can just proceed with the composer installation which will pick this new version. If you were
using major version 2 in the past, edit your `composer.json` file manually to use the new version `^3.0`. Alternatively, update your `composer.json` by using the command `composer require yireo/magento2-googletagmanager2:^3.0 --no-update`. Next, upgrade with `composer update`.

## Server-side tracking
Next to pushing events to the `dataLayer` in the browser, this extension can send ecommerce events server-to-server,
using the [GA4 Measurement Protocol](https://developers.google.com/analytics/devguides/collection/protocol/ga4).
Server-side events are not blocked by ad blockers and are also sent when the customer never reaches the success page
(for instance with offsite payments, payment webhooks or orders created in the Admin Panel).

### Getting started
1. **Check the requirements**: You need a GA4 property, a GTM web container with a GA4 tag that uses the **same**
   Measurement ID (so that the `_ga` cookies exist in the browser) and a running Magento cron
   (`bin/magento cron:run` every minute).
2. **Create an API secret in GA4**: Go to **Admin > Data streams**, select your web stream, open
   **Measurement Protocol API secrets** and click **Create**. Copy the secret value.
3. **Configure Magento**: Go to **Stores > Configuration > Yireo > Yireo GoogleTagManager > Server-Side Tracking** and
   set:
    - **Enabled**: *Yes*
    - **Destination**: *Google Analytics 4 (Measurement Protocol)*
    - **Measurement ID**: your `G-XXXXXXX` ID
    - **API secret**: the secret from step 2
    - **Events**: keep the default `purchase` and `refund`
    - **Validation mode**: *Yes* (for now)
4. **Flush the cache**: `bin/magento cache:flush`
5. **Send a test event**: `bin/magento yireo_googletagmanager2:serverside:test --store=1`. You should see
   `Test event sent successfully`. If not, check the Measurement ID and the API secret.
6. **Place a test order** and check that the `purchase` event is in the outbox with the status `sent` (within a minute,
   after cron has run):
   ```sql
   SELECT entity_id, event_name, dedup_key, status, attempts, last_error FROM yireo_gtm_serverside_event ORDER BY entity_id DESC;
   ```
   Enable **Debug** in the group **Settings** to see every request and response in `var/log/yireo_googletagmanager2.log`.
7. **Go live**: Set **Validation mode** to *No* and flush the cache. Place another order and check
   **Reports > Realtime** in GA4.
8. **Optional**: With offsite or delayed payments, set **Purchase trigger** to *When an invoice is paid*, so that
   only paid orders are sent.

That's it. By default, the `purchase` event is no longer pushed to the `dataLayer` in the browser, so purchases are
not counted twice.

### Configuration
All settings are found in **Stores > Configuration > Yireo > Yireo GoogleTagManager > Server-Side Tracking**:

- **Enabled**: Enable server-side tracking. Nothing is sent unless both a Measurement ID and an API secret are configured.
- **Destination**:
    - *Google Analytics 4 (Measurement Protocol)*: Events are sent to `https://www.google-analytics.com/mp/collect`.
    - *Server-side GTM container*: Events are sent to `<Server-side GTM URL>/mp/collect`. Your sGTM container needs a
      Measurement Protocol client to handle these requests.
- **Measurement ID** (`G-XXXXXXX`) and **API secret**. The API secret is stored encrypted and is never logged.
- **Events**: The events to send server-side: `purchase` and `refund` (default), plus optionally `add_to_cart`,
  `remove_from_cart`, `begin_checkout`, `add_shipping_info`, `add_payment_info`, `login`, `sign_up` and
  `add_to_wishlist`.
- **Purchase trigger**: When to send the `purchase` event:
    - *When the order is placed* (default)
    - *When the order reaches one of the configured order states* (setting **Orders states for purchase event** in
      the group **Settings**)
    - *When an invoice is paid*
- **Suppress browser purchase event** (default *Yes*): When the `purchase` event is sent server-side, it is no longer
  pushed to the `dataLayer` in the browser.
- **Dispatch mode**:
    - *Cron* (default): Pending events are sent by the cron job `yireo_gtm2_serverside_process` every minute.
    - *Message queue*: Every event is published to the topic `yireo_gtm2.serverside.event` (connection `db`). Make
      sure the consumer is running: `bin/magento queue:consumers:start yireo_gtm2.serverside.event`.
- **Require analytics consent** (default *Yes*): Only applies when the consent banner of this extension is enabled.
  Events are only sent when the customer granted analytics consent.
- **Validation mode**: Send events to the validation endpoint (`/debug/mp/collect`) instead. GA4 does not record
  these events. Validation messages are logged when **Debug** is enabled.
- **Maximum attempts** (default 5) and **Retention of sent events (days)** (default 30).

The older settings **Custom GTM container URL** (`settings/serverside_enabled`) and **Container URL**
(`settings/serverside_gtm_url`) only change the URL that the GTM container is loaded from in the browser. They do not
send any data server-to-server.

### Tips and limitations
- **Do not double count**: Events like `add_to_cart` or `begin_checkout` are still pushed to the `dataLayer` in the
  browser as well. Only enable them server-side if your GTM web container does not send the same events to the same
  GA4 property.
- **Keep cron running**: In *Cron* mode, nothing is sent without cron. Because the browser `purchase` event is
  suppressed, a stopped cron means that purchases are only sent once cron runs again.
- **Mind the 72 hours**: GA4 only accepts events up to 72 hours old. Events are retried every 15 minutes until the
  maximum number of attempts is reached, so fix outages quickly.
- **Use the same Measurement ID** in the browser and in this configuration, otherwise server-side events can not be
  linked to the session of the visitor.
- **Consent**: The consent check only works with the consent banner of this extension. When you use a third-party
  consent tool, either set **Require analytics consent** to *No* or make sure that it calls
  `yireoGoogleTagManager2SetConsent()`.
- **Orders without a browser**: For orders created in the Admin Panel (or without stored browser context), a random
  `client_id` is used. With **Require analytics consent** enabled, these events are skipped, because consent is
  unknown.

### How it works
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

### CLI
```bash
bin/magento yireo_googletagmanager2:serverside:test --store=1        # Send a test event to the validation endpoint
bin/magento yireo_googletagmanager2:serverside:test --store=1 --live # Send a test event to the live endpoint
bin/magento yireo_googletagmanager2:serverside:process --limit=100   # Send pending events and retry failed events
```

## See also
- [Installation](INSTALL.md)
- [Usage](USAGE.md)
- [Tutorial](TUTORIAL.md)
- [Tutorial with Consent](TUTORIAL_CONSENT.md)
- [FAQ](FAQ.md)
- [Architecture](ARCHITECTURE.md)
- [Testing](TESTING.md)
- [CHANGELOG](CHANGELOG.md)
- [License](LICENSE.txt)


## Support & Professional Services

This extension is released as **free and open source software**.  
You are free to use, study, modify and distribute the code according to the license terms.

While the extension itself is available at no cost, professional assistance is available for merchants, agencies and developers who require guaranteed help, customizations or consultancy.

### Professional support

Commercial support, training and consultancy can be obtained through **Yireo**:

- Magento 2 consultancy and architecture guidance
- Custom feature development
- Debugging and performance optimization
- Implementation support and best-practice advice
- Developer training and workshops

If you require professional support, please contact Yireo:

👉 https://www.yireo.com/

Yireo is a Dutch company focused on Magento development, training and extension development, actively maintaining various open-source Magento projects. 


## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GoogleTagManager2/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GoogleTagManager2/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GoogleTagManager2/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GoogleTagManager2/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GoogleTagManager2/compile.yml?label=compile)
<!-- badges.test.end -->
