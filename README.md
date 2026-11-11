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

## Table of contents
- [Installation](#installation)
- [Usage](#usage)
- [Tutorial](#tutorial)
- [Tutorial with Consent](#tutorial-with-consent)
- [Server-Side Tracking](#server-side-tracking)
- [PWA compatibility](#pwa-compatibility)
- [FAQ](#faq)
- [Architecture](#architecture)
- [Testing](#testing)
- [CHANGELOG](CHANGELOG.md)
- [Security](SECURITY.md)
- [License](LICENSE.txt)

## Installation
### Minimum requirements
We are using strict requirements in the `composer.json` file, to make sure compatibility follows from the `composer` requirements. When you have questions regarding compatibility, install the extension via `composer`. If it works, it is because it is compatible. If it does not work, it is because it is not compatible. When a new version of Magento comes out and the `composer` command fails, while you think it should actually work, open a support ticket so we can look into this.

### Instructions for using composer
Use composer to install this extension. First make sure that Magento is installed via composer, and that there is a valid `composer.json` file present.

Next, install our module using the following command:

    composer require yireo/magento2-googletagmanager2

Next, install the new module into Magento itself:

    bin/magento module:enable Yireo_GoogleTagManager2
    bin/magento cache:clean

There is no need to run `setup:upgrade` because this module does not require a database upgrade.

### Instructions for manual copy
We recommend `composer` to install this package. Also, the method described here is not supported. If you are less knowledgeable on how to install extensions, use `composer` instead. If you want a manual copy instead, these are the steps.

Download a ZIP of this repository. Extract the files. Upload the files to the folder `app/code/Yireo/GoogleTagManager2` of your site.

Next, install the new module into Magento itself:

    bin/magento module:enable Yireo_GoogleTagManager2
    bin/magento cache:clean

### Removing the extension
If you are not the person having added this extension, we very strongly recommend you to have a technical person look into the deinstallation of our module. Deinstalling Magento modules is a technical matter, peanuts for a developer and our extension is not different in any way.

If you have installed this extension via `composer`, simply follow the `composer` procedure again:

    composer remove yireo/magento2-googletagmanager2

If you have copied files to `app/code/Yireo/GoogleTagManager2/`, remove them.

Next, follow your deployment procedure to copy changes to your live site. Theoretically, you can modify the `core_config_data` configuration table to remove configuration values from there. Note that Magento does not offer a solid uninstall procedure for this anyway. Double-check that the module has been removed from the `app/etc/config.php` file as well.

## Usage
### Configuration
Login to your [Google Tag Manager](http://www.google.com/tagmanager/) account. Follow the Google instructions to
create a new Google Tag Manager **container**. Extract the **Container Public ID** from your new container. Configure
your new ID in Magento through **Admin Panel > Stores > Configuration > Yireo > Yireo GoogleTagManager**.

### Features
The extension has the following configuration options:

- **Enabled**: When this is set to No, the extension does not work.
- **Container Public ID**: The ID of your Google Tags container (starting with `GTM-`).
- **Debug**: Enable this for additional debugging in a logfile and the browser console.
- **Debug Clicks**: Enable this for additional debugging when clicking on specific links.
- **Maximum Products in Category**: Instead of listing all products on a specific category page, only list the first products.
- **Product EAV Attributes**: Product attributes to include when listing products in a specific event.
- **Category EAV Attributes**: Category attributes to include when listing categories in a specific event.
- **Customer EAV Attributes**: Customer attributes to include when listing customers in a specific event. Currently this is not implemented fully because of privacy regulations.

### Implemented events
The following events are implemented in the current version:

- `add_payment_info`
- `add_shipping_info`
- `add_to_cart`
- `add_to_wishlist`
- `begin_checkout`
- `login`
- `logout`
- `purchase`
- `remove_from_cart`
- `select_item`
- `sign_up`
- `view_cart`
- `view_item_list`
- `view_item`
- `view_search_result`

If you think an event is missing, post a GitHub Issue to kickstart such an initiative.

The following events are not implemented because a lack of inspiration:
- `view_promotion`
- `select_promotion`

The following events should actually be implemented via a server-side implementation:
- `refund`

### Tip: Browser extension
Use the [DataLayer Checker](https://chrome.google.com/webstore/detail/datalayer-checker/ffljdddodmkedhkcjhpmdajhjdbkogke) for Chrome to easily see what kind of data is sent from Magento to Google Tag Manager.
 
### Tip: CheckoutTester2
When you want to track conversions in your Magento checkout, our extension helps out as well: It adds the relevant information to all your checkout and cart pages. Do you want to know which variables are on the success page? Use the [Yireo CheckoutTester](https://github.com/yireo/Yireo_CheckoutTester2) extension to preview that page and view its HTML source.

Bring your towel.

## Tutorial
This guide helps you setup with Google Analytics and Google Tag Manager. Note that this guide was written without being a Google expert and only to make the Magento extension work as quickly as possible. If you get stuck while reading this tutorial, it could be that the wording of this tutorial could be improved: Feel free to make a Pull Request. It could also be that you are lacking knowledge for using the Google consoles. Unfortunately, we are not providing support for either Google Tag Manager and Google Analytics, we are only providing technical support for this Magento extension.

First of all, make sure you have existing accounts for both [Google Analytics](https://analytics.google.com/) and [Google Tag Manager](https://tagmanager.google.com/). The idea is to make sure this Magento extension sends data to Google Tag Manager, which then forwards this traffic to Google Analytics. Best is to start with configuring Google Analytics.

### Google Analytics
Login into Google Analytics. Create a new **App** for your site. Within that **App**, under **Admin**, navigate to **Data Streams**. Create a new **Web** stream. Under the **Web** stream details, write down the **Measurement ID** (starting with `G-`) for later use.

### Google Tag Manager
Login into Google Tag Manager.

Create a new **Container** for your site. Write down the **Container ID** (starting with `GTM-`) to configure later in the Magento extension.

In the **Container Workspace**, under **Tags**, create a new **Tag**. As **Tag Configuration** type, choose **Google Analytics > Google Tag**. Fill in the Google Analytics **Measurement ID** (starting with `G-`). Keep the **Send a page view event when this configuration loads** option checked. Make sure to trigger this tag on all pages with the **All pages** trigger.

In the **Container Workspace**, under **Variables** and then **Built-In Variables**, make sure the following variables are enabled:

- **Page URL**
- **Page Hostname**
- **Page Path**
- **Referrer**
- **Event**
- And possibly others

In the **Container Workspace**, under **Variables** and then **User-Defined Variables**, create the following variables of type **Data Layer Variable** and Data Layer Version 2:

- Variable with label `Ecommerce` and name `ecommerce`
- Variable with label `Ecommerce Affiliation` and name `ecommerce.affiliation`
- Variable with label `Ecommerce Coupon` and name `ecommerce.coupon`
- Variable with label `Ecommerce Currency` and name `ecommerce.currency`
- Variable with label `Ecommerce Shipping` and name `ecommerce.shipping`
- Variable with label `Ecommerce Value` and name `ecommerce.value`
- Variable with label `Ecommerce Tax` and name `ecommerce.tax`
- Variable with label `Ecommerce Transaction ID` and name `ecommerce.transaction_id`

Also create a variable of type **Custom JavaScript** with label `Ecommerce Items` and the following custom JavaScript:
```js
function() {
  var ecom = {{Ecommerce}};
  if ( ecom && ecom.items ) {
    return ecom.items;
  } else {
    return undefined;
  }
}
```

In the **Container Workspace**, under **Triggers**, create a trigger "Ecommerce Custom Event", which fires on **All Custom Events**. Select the checkbox "Use regex matching" and input `view_item|view_item_list|select_item|add_to_cart|remove_from_cart|view_cart|begin_checkout|add_payment_info|add_shipping_info|purchase` in the **Event** name.

In the **Container Workspace**, under **Tags**, create a second **Tag**, this time of type **Google Analytics > GA4 Events**. Set **Event Name** to `{{Event}}`. Next, enter the following **Event Parameters**:

- Parameter name `items` with value `{{Ecommerce Items}}`
- Parameter name `transaction_id` with value `{{Ecommerce Transaction ID}}`
- Parameter name `affiliation` with value `{{Ecommerce Affiliation}}`
- Parameter name `value` with value `{{Ecommerce Value}}`
- Parameter name `tax` with value `{{Ecommerce Tax}}`
- Parameter name `shipping` with value `{{Ecommerce Shipping}}`
- Parameter name `currency` with value `{{Ecommerce Currency}}`
- Parameter name `coupon` with value `{{Ecommerce Coupon}}`

Under **More Settings**, enable the flag **Send Ecommerce data** and use as the **Data Source** the option **Data Layer**. Make sure to trigger this second tag with custom trigger **Ecommerce Custom Event**.

Once the two tags are configured in Google Tag Manager, use **Publish** to publish your new configuration as a new version.

**Alternatively:**

Create the **GA4 Configuration** tag manually like described above. Next, instead of manually creating the **Variables**, **Ecommerce Custom Event** trigger and **GA4 Events** tag, download [this JSON file](https://raw.githubusercontent.com/yireo/Yireo_GoogleTagManager2/master/docs/gtm-example.json). Edit it manually and replace the following strings with your own:

- `ACCOUNT_ID` should be replaced with your own numeric account ID (visible in GTM URL)
- `CONTAINER_ID` should be replaced with your own numeric container ID (visible in GTM URL)
- `CONTAINER_NAME` should be replaced with your own container name
- `GTM_PUBLIC_ID` should be replaced with your own container public ID (starting with `GTM-`)
- `MEASUREMENT_ID` should be replaced with your own GA4 Measurement ID (starting with `G-`)

### Magento
The following steps assume that the Yireo module was already installed and enabled properly. Within the Magento Admin Panel, navigate to the **Store Configuration** and open up the **Yireo GoogleTagManager** options (under the section **Yireo**). Make sure the option **Enabled** is set to **Yes**. Configure the Google Tag Manager key starting with `GTM-` under **Container Public ID**. Enable **Debug** to make sure everything is working fine.

### Previewing & debugging
If all steps were followed correctly (and debugging is enabled in the Magento extension), you should be able to see debugging messages in the **Error Console** of your browser, once you navigate through your frontend. If no error messages are mentioned and the Google Tag Manager key seems to be correct as well, this should mean that data from Magento should be sent correctly to Google Tag Manager.

In Google Analytics, go to **Reports**: There should be 1 user reported there. Next, under **Reports > Realtime**, you should be seeing real-life traffic coming in. Likewise, under **Engagement > Events**, there should be multiple events. Note that these reports (except for the **Realtime** report) could be taking 24 hours to be detected. Use the **Realtime** page to get direct results.

In the Google Tag Manager, use **Preview** in your workspace to open up a new browser window to your Magento shop in debugging mode. Next, within Google Analytics, use the **DebugView**. Besides generic events like `pageview`, there should also be more specific e-commerce events like `view_item_list` and `view_item`. 

## Tutorial with Consent
Read the [Tutorial](#tutorial) to get yourself familiar with it. This will only substitute the `Alternatively` and `Magento` parts.

Have a look at the docs for [Tag Manager](https://support.google.com/tagmanager/answer/10718549) to see how to enable & configure consent.

### Alternatively
Download [this JSON file](https://raw.githubusercontent.com/yireo/Yireo_GoogleTagManager2/master/docs/gtm-consent-example.json). Edit it manually and replace the following strings with your own:

- `ACCOUNT_ID` should be replaced with your own numeric account ID (visible in GTM URL)
- `CONTAINER_ID` should be replaced with your own numeric container ID (visible in GTM URL)
- `CONTAINER_NAME` should be replaced with your own container name
- `GTM_PUBLIC_ID` should be replaced with your own container public ID (starting with `GTM-`)

After importing the adjusted JSON file make sure to check all tags and variables. Publish the new configuration.

### Magento
The following steps assume that the Yireo module was already installed and enabled properly. 
Within the Magento Admin Panel, navigate to the **Store Configuration** and open up the **Yireo GoogleTagManager** options (under the section **Yireo**). 
Make sure the option **Enabled** is set to **Yes** within the consent group.

### Show cookie-settings button
If you want to show the cookie-settings button, if for example your footer add this code:
```html
<a onclick="yireoGoogleTagManager2BannerToggle()" href="#">Cookie-settings</a>
```
Or for Hyva theme:
```html
<a x-data="" @click="$dispatch('yireo-show-consent-banner')" class="cursor-pointer">Cookie-settings</a>
```

## Server-Side Tracking
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


## PWA compatibility
This frontend extension will be completely replaced by React or Vue functionality, and therefore, this extension will not be made compatible with PWA.

## FAQ
### Does this extension work together with Hyvä?
Yes, this extension ships with native JavaScript code that works with Hyvä. Additionally, the module ships with Hyvä-specific XML layout handles. Note that this applies to the Yireo GoogleTagManager2 version 3. With version 2, you will still need the [compatibility module](https://gitlab.hyva.io/hyva-themes/hyva-compat/magento2-yireo-googletagmanager2/). 

Currently, Luma-based checkouts work without an issue, but for using the React-based checkout or the MageWire-based checkout, more work is needed.

### This extension is not generating any `purchase` event
First of all, make sure to upgrade to the latest version of this extension, before reporting any issue. 

When making use of [server-side tracking](#server-side-tracking), check the **Purchase trigger** and the outbox table `yireo_gtm_serverside_event`. Otherwise, make sure to customize the setting **Orders states for purchase event** (`order_states_for_purchase_event`) and to manually trigger the `purchase` event where needed.

### The purchase event is fired when the order hasn't been completed yet
The Yireo GoogleTagManager extension uses the core event `sales_order_place_after` to trigger the `purchase` event. And this event is passed on from the server-side (PHP) towards the client-side (JavaScript) whenever a page is hit again (for instance, the loading of the checkout success page) or whenever an AJAX call occurs. At this moment, it might be that the payment has not yet occurred, so that the order status is not yet complete, but still the `purchase` event is triggered. This is **not** a bug, this is where you step in as an integrator of this Yireo GoogleTagManager2 extension and your custom choice of PSPs.

How do you want this to work? Firing the `purchase` event on the success page will often **not** work either: It could be that a payment is delayed, so that the PSP only notifies the Magento application of the payment (so that the order can be completed) *after* the customer has left the shop. A similar scenario pops up when you are placing an order in the Magento Admin Panel. In these scenarios, there no longer is a scenario where client-side tracking (via JavaScript in the frontend) can be used. Instead, the solution is server-side tracking.

The Yireo GoogleTagManager2 extension can send the `purchase` event server-to-server via the GA4 Measurement Protocol, for instance only when an invoice is paid or when the order reaches a specific state. See [Server-Side Tracking](#server-side-tracking) to get started.

If you need any additional help, the company of [Yireo](https://www.yireo.com/) is happy to help out with a custom (paid) consult as well.

### Does this extension work under PHP 8.2?
Yes, version 3 does. Version 2 is no longer maintained, but you could use the following composer patch with `vaimo/composer-patches`:
```bash
{
  "patches": {
     "yireo/magento2-googletagmanager2": {
        "Fix PHP 8.2 error": {
          "source": "https://patch-diff.githubusercontent.com/raw/yireo/Yireo_GoogleTagManager2/pull/136.diff",
          "link": "https://github.com/yireo/Yireo_GoogleTagManager2/pull/136",
          "level": 1
        }
      }
  }
}
```

### Does this extension work together with OneStepCheckout?
Yes, this extension works nicely together with [onestepcheckout.com](https://www.onestepcheckout.com/)

### I've installed the module but nothing happens
Make sure that both Google Analytics and Google Tag Manager are properly configured. See our [tutorial](#tutorial) for additional guidance. Make sure the module settings in Magento are properly configured: The flag **Enabled** sets to be set to **Yes**. The **Container Public ID** needs to start with `GTM-` (unless you are testing in the Developer Mode). Optionally debugging can be enabled, which should print various messages in the **Console** of your browser. Refresh all Magento caches. Within the **Network** tab of your browsers Developer Tools, you should be able to see an outgoing request to `https://www.googletagmanager.com/`.

### Revenue is not showing up
Sometimes it is reported to us that with our module, revenue does not show up properly in the GA panel. With all reports so far, this turned out to be an incorrect revenue setting in GA panel, instead of something related to this Magento module. Note that the responsibility of this module is to deliver a `purchase` event. If that `purchase` event is showing in GA properly, then the job of this module is done. However, it is the responsibility of GA to calculate the right revenue from all purchases.

### How to extend upon the Data Layer created by this module?
The main methodology for this module to generate its Data Layer by using the XML layout. The block with name `yireo_googletagmanager2.data-layer` contains a `data-layer` argument that could be modified and extended with other XML layout files.

### How to add additional fields to product impressions?
A separate class `\Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper` is used to supply the fields per product impression dynamically. You can create a DI plugin to intercept the return value of the method `mapByProduct()` (by creating a DI plugin method `afterMapByProduct()`): Each entry in the returned array has a key that refers to the data property of the product (EAV attribute) and a value that refers to the Data Layer field (or `impressionField`). 

For example, you could declare a DI plugin within your own `di.xml`, create a DI plugin class and use a method like the following:
```php
public function afterMapByProduct(ProductDataMapper $productDataMapper, array $productData, ProductInterface $product): array 
{
    $productData['foo'] = 'bar';
    return $productData;
}
```

### I'm getting CSP errors with this extension
We have tested this extension with out-of-the-box functionality thoroughly upon CSP errors. And the chance that there are CSP errors
related to this extension itself is small.

Note that non-developers can easily add new third-party scripts to the Google Tag Manager console (so not within Magento, but from
their click-click Google account) which then would cause issues with CSP. This is the goal of CSP: Make sure that third-party
scripts are only allowed if the CSP policy is modified to accommodate them. This CSP concept has zero to do with the Magento
extension though.

## Architecture
Note that this document only applies to version 3.0 and higher.

### Extensibility in general
- XML layout arguments
- Injectable interfaces (with a preference) in `Yireo\GoogleTagManager2\Api\`
- Non-injectable interfaces to enforce certain behaviour in `Yireo\GoogleTagManager2\Api\Data\`

Don't touch anything else directly in the code of this extension - don't make core hacks. If you want to add something in a way that is not supported, open a GitHub Issue and let's discuss how to support it anyway.

### DataLayer definition
The datalayer that is sent to Google Tag Manager is in general built up in two ways: Via inline scripts that are inserted into the HTML of pages and via external JavaScript files. 

The inline scripts are based on a ViewModel `Yireo\GoogleTagManager2\ViewModel\DataLayer` that is added (under the argument `data_layer_view_model`) to a block named `yireo_googletagmanager2.data-layer`. The block also adds two other arguments: `data_layer` and `data_layer_processors`. Both arguments are used by the ViewModel to build up the datalayer. The argument `data_layer` contains a static definition, derived from XML layout. The argument `data_layer_processors` contains a listing of PHP processors that could modify the static `data_layer` afterwards.

### XML layout argument `data_layer`
The XML layout argument `data_layer` is used to generate the initial datalayer (as part of the HTML document) and it should contain therefore only values (or tags) that are cacheable. In other words, values related to customer sessions or checkout sessions should be picked up in JavaScript instead.

The `data_layer` in the XML layout is an array of items, where each item could be of various types `xsi:type`: Strings, numeric values, booleans. Whatever ends up in this array is converted into JSON that is pushed as the first datalayer. See the file `view/frontend/layout/default.xml` for a jumpstart.

Thanks to the fact that the XML layout is extensible by modules and themes, this datalayer of the Yireo GoogleTagManager2 module is fully extensible as well. This is shown in various additional handles, as part of this module:

### Tag classes
A tag `version` could be added to the datalayer with an XML layout statement like the following:
```xml
<argument name="data_layer" xsi:type="array">
    <item name="version" xsi:type="string">0.1.2</item>
</argument>
```

If the `xsi:type` is `object`, then the value could refer to a class. A restriction within the Yireo GoogleTagManager2 is that this class implements `Yireo\GoogleTagManager2\Api\Data\TagInterface` (which again implements the generic `Magento\Framework\View\Element\Block\ArgumentInterface` interface):

```xml
<argument name="data_layer" xsi:type="array">
    <item name="version" xsi:type="object">Yireo\GoogleTagManager2\DataLayer\Tag\Version</item>
</argument>
```

In the example above, the tag class `Yireo\GoogleTagManager2\DataLayer\Tag\Version` implements the interface `Yireo\GoogleTagManager2\Api\Data\TagInterface` and it returns a simple string (so, the version) via the implemented `get()` method. This string is then added as `version` into the datalayer. In other words, the item name in the XML layout determines the name of the datalayer property.

If you have a tag class that returns an `array` and you want the keys and the values of that array to be merged directly into the current datalayer, implement the interface the `Yireo\GoogleTagManager2\Api\Data\MergeTagInterface` interface instead.

The namespace `Yireo\GoogleTagManager2\DataLayer\Tag` contains all tag classes, offered out-of-the-box. If you want to add more values, you can add them through the XML layout. You can do this through a theme. If you also want to add your own custom tag class, we recommend you to create a module instead, making it dependent on the `Yireo_GoogleTagManager2` module (both in your `composer.json` file and your `etc/module.xml` file) and use the tag interfaces as mentioned.

### Data layer processors
On top of the layout-based datalayer approach, which is already quite flexible, you can also parse entries via data layer processors as well: Classes that are defined through the XML layout argument `data_layer_processors` of the main block and are implementing `\Yireo\GoogleTagManager2\DataLayer\Processor\ProcessorInterface`: 

```xml
<referenceBlock name="yireo_googletagmanager2.data-layer">
    <arguments>
        <argument name="data_layer_processors" xsi:type="array">
            <item name="category" xsi:type="object">Yireo\GoogleTagManager2\DataLayer\Processor\Category</item>
        </argument>
    </arguments>
</referenceBlock>
```

The recommendation is to use this as a last resort. And again, make sure to realize that this approach results in a datalayer that should be cacheable: No private content is allowed!

### Main JavaScript file and `customerData`
On every page, an additional JavaScript file `Yireo_GoogleTagManager2::js/generic.js` is loaded to dynamically add tags that are private, like checkout-related information and customer-specific details. The logic here relies upon the Magento `customerData` logic that provides information to JavaScript via an AJAX call to the URL `customer/section/load` (which returns JSON) which is then cached in `localStorage`. 

The `customerData` sections `cart` and `customer` are extended with DI plugin interceptors to add a subarray `gtm` which is then picked up in JavaScript (see `etc/frontend/di.xml`). If you want to add more data than provided by default, either create your own DI plugin interceptors in your own module. Or use one of the session data providers elsewhere in your code (like an observer).

The two session data providers - `\Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface` and `\Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface` - only serve one purpose: To temporarily hold information that is added to the DI plugin interceptors for the corresponding `customerData` sections. They contain an easy method like `append()`. If you would use `append(['foo' => 'bar'])`, then the DI plugin will add a tag `foo` with value `bar` to the datalayer.

### JavaScript mixins for datalayer events
A RequireJS mixin is added to `Magento_Catalog/js/catalog-add-to-cart` to trigger the datalayer event `addToCart`.

### Observer for `removeFromCart` event
The datalayer event `removeFromCart` is ideally triggered from JavaScript as well, but unfortunately the JavaScript API is not consistent. Instead, the event `sales_quote_remove_item` is observed by an observer which calls upon the session data provider `\Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface` to temporarily add the event data to the session and then append this to the `customerData` section. Right.

### Handling product clicks
Product clicks are tracked through a specific template called `script-product-clicks`. Make sure the XPath identifier used as the XML layout argument `product_path` is matching with yours. By default, this is `.product-items a.product` in Luma. To change this, use an XML layout instruction like the following:

```xml
<referenceBlock name="yireo_googletagmanager2.script-product-clicks">
    <arguments>
        <argument name="product_path" xsi:type="string">.my-products a.product</argument>
    </arguments>
</referenceBlock>
```

### Extending or changing data mappers
In various classes in the namespace `Yireo\GoogleTagManager2\DataLayer\Mapper`, like the `Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper` class, data is being fetched and mapped towards the structure that GTM expects (as we see it). If you want the structure to be different, you can use a DI type to hook into the constructor argument `dataLayerMapping` and add your own class:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper">
        <arguments>
            <argument name="dataLayerMapping" xsi:type="array">
                <item name="example" xsi:type="object">Yireo\Example\DataLayer\Mapper\AdditionalProductDataMapper</item>
            </argument>
        </arguments>
    </type>
</config>
```

Next, your custom class `Yireo\Example\DataLayer\Mapper\AdditionalProductDataMapper` needs to implement the interface `Yireo\GoogleTagManager2\Api\Data\ProductTagInterface` (specific for this product data mapping) and/or `Yireo\GoogleTagManager2\Api\Data\TagInterface` (for generic data mapping).

The XML property name `example` is the tag property added to GTM. If an existing property name, like `item_id`, is used, it will override the existing tag value.

### Server-side tracking
The namespace `Yireo\GoogleTagManager2\ServerSide` sends events via the GA4 Measurement Protocol. Browser events that
are added to the checkout or customer session (`CheckoutSessionDataProviderInterface::add()` and
`CustomerSessionDataProviderInterface::add()`) are picked up by a plugin and published server-side as well. Order
events (`purchase`, `refund`) are triggered by observers in `etc/events.xml`. All events go through
`ServerSide\EventPublisher` into the outbox table `yireo_gtm_serverside_event`, which is processed by cron or by the
message queue consumer `yireo_gtm2.serverside.event`. See [Server-Side Tracking](#server-side-tracking).

## Testing
### Unit testing
This extension ships with PHPUnit tests. The generic PHPUnit configuration in Magento 2 will pick up on these
tests. To only test Yireo extensions, simply run PHPUnit from within this folder. Note that this assumes that
the extension is installed via composer. For instance:

    phpunit

Also note that Mockery (`mockery/mockery`) is used for the integration tests. If you want to test this module, you need to install its dev-dependencies:

    composer require yireo/magento2-googletagmanager2 --dev

The JavaScript code ships with MochaJS unit tests. To install the stuff, simply run (within this extension directory) the following:

    npm install
    npm run mocha

Or just use `yarn`:

    yarn
    npm run mocha



### Playwright testing
The folder `Test/Playwright` contains Playwright tests. They are run with the Playwright setup of the
`loki-checkout/magento2-functional-tests` package (which also provides the endpoints used to configure the shop and
to add a product to the cart):

    composer require loki/magento2-functional-tests
    bin/magento module:enable Loki_FunctionalTests
    bin/magento loki:modules:dump
    cd vendor/loki/magento2-functional-tests/Test/Playwright/
    npm install
    npx playwright test --project=Yireo_GoogleTagManager2

The folder `Test/Playwright/lib` contains test objects for the GTM `dataLayer`. The `test` exported from
`lib/gtm-objects.ts` adds a `dataLayer` fixture, which stubs all requests to `googletagmanager.com` so that GTM itself
does not modify the `dataLayer`:

```ts
import {test, expect, configureGtm, GTM_ID} from './lib/gtm-objects';

test('product page', async ({page, dataLayer}) => {
    await configureGtm(page, {config: {'googletagmanager2/settings/wait_for_ui': 0}});
    await page.goto('/some-product.html');

    await dataLayer.expectContainerLoaded(GTM_ID);
    await dataLayer.expectState({page_type: 'product'});

    const viewItem = await dataLayer.event('view_item').toHaveValidEcommerce();
    await dataLayer.event('add_to_cart').where({ecommerce: {currency: 'USD'}}).notToBePushed();
    await dataLayer.expectEventOrder(['view_item']);
});
```

Assertions on events poll the `dataLayer`, so events that are pushed asynchronously (for instance via customer
sections) are picked up as well.

#### Configuring the shop

The second argument of `configureGtm()` is the payload of the `loki_functional_tests/index/configure` endpoint, so it
accepts everything `Loki\FunctionalTests\Service\Configurator` understands. Store settings go under `config`, where
they are merged on top of `defaultConfig`, and every other key is passed on untouched:

```ts
await configureGtm(page, {
    product: true,
    config: {
        'googletagmanager2/settings/view_cart_occurances': 'cart_page',
    },
});
```

| Key | Purpose |
|---|---|
| `config` | Store configuration, as `path => value`. Merged on top of `defaultConfig`. |
| `product` | `true` makes sure a saleable product exists, creating a dummy one if the catalog is empty. Pass an object (`{sku, name, price, qty, ...}`) to control it. |
| `customer` | Customer to create and/or log in. |
| `address` | Fields written to both the billing and shipping address of the current quote. |
| `modules` | Modules to enable. |
| `secure_config` | Configuration written through the encrypted backend model. |

Keys that are left out are not touched at all, so a test only states what it actually needs.

#### Sample data
`gtm-category-data-test.spec.ts` checks the category data of products (`item_list_id`, `item_list_name`,
`item_category` ... `item_category5`) on category pages, product pages and in the cart. It relies on the Luma sample
data (the product "Didi Sport Watch" in the category "Gear > Watches") and is skipped when that product is not
available. Make sure the search index is up to date, otherwise category pages show no products.

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
