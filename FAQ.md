# Does this extension work together with Hyvä?
Yes, this extension ships with native JavaScript code that works with Hyvä. Additionally, the module ships with Hyvä-specific XML layout handles. Note that this applies to the Yireo GoogleTagManager2 version 3. With version 2, you will still need the [compatibility module](https://gitlab.hyva.io/hyva-themes/hyva-compat/magento2-yireo-googletagmanager2/). 

Currently, Luma-based checkouts work without an issue, but for using the React-based checkout or the MageWire-based checkout, more work is needed.

# This extension is not generating any `purchase` event
First of all, make sure to upgrade to the latest version of this extension, before reporting any issue. 

When making use of Server-Side Tracking, make sure to customize the setting **Orders states for purchase event** (`order_states_for_purchase_event`) and to manually trigger the `purchase` event where needed.

# The purchase event is fired when the order hasn't been completed yet
The Yireo GoogleTagManager extension uses the core event `sales_order_place_after` to trigger the `purchase` event. And this event is passed on from the server-side (PHP) towards the client-side (JavaScript) whenever a page is hit again (for instance, the loading of the checkout success page) or whenever an AJAX call occurs. At this moment, it might be that the payment has not yet occurred, so that the order status is not yet complete, but still the `purchase` event is triggered. This is **not** a bug, this is where you step in as an integrator of this Yireo GoogleTagManager2 extension and your custom choice of PSPs.

How do you want this to work? Firing the `purchase` event on the success page will often **not** work either: It could be that a payment is delayed, so that the PSP only notifies the Magento application of the payment (so that the order can be completed) *after* the customer has left the shop. A similar scenario pops up when you are placing an order in the Magento Admin Panel. In these scenarios, there no longer is a scenario where client-side tracking (via JavaScript in the frontend) can be used. Instead, the solution is server-side tracking.

The Yireo GoogleTagManager2 extension does not offer server-side tracking as a functionality, but it does support it. Simply enable the setting **Server-Side Tracking**, configure the **Container URL** and select which order states should trigger the `purchase` event (most likely only the `complete` status).

If you need any additional help, the company of [Yireo](https://www.yireo.com/) is happy to help out with a custom (paid) consult as well.

# Does this extension work under PHP 8.2?
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

# Does this extension work together with OneStepCheckout?
Yes, this extension works nicely together with [onestepcheckout.com](https://www.onestepcheckout.com/)

# I've installed the module but nothing happens
Make sure that both Google Analytics and Google Tag Manager are properly configured. See our [tutorial](/TUTORIAL.md) for additional guidance. Make sure the module settings in Magento are properly configured: The flag **Enabled** sets to be set to **Yes**. The **Container Public ID** needs to start with `GTM-` (unless you are testing in the Developer Mode). Optionally debugging can be enabled, which should print various messages in the **Console** of your browser. Refresh all Magento caches. Within the **Network** tab of your browsers Developer Tools, you should be able to see an outgoing request to `https://www.googletagmanager.com/`.

# Revenue is not showing up
Sometimes it is reported to us that with our module, revenue does not show up properly in the GA panel. With all reports so far, this turned out to be an incorrect revenue setting in GA panel, instead of something related to this Magento module. Note that the responsibility of this module is to deliver a `purchase` event. If that `purchase` event is showing in GA properly, then the job of this module is done. However, it is the responsibility of GA to calculate the right revenue from all purchases.

# How to extend upon the Data Layer created by this module?
The main methodology for this module to generate its Data Layer by using the XML layout. The block with name `yireo_googletagmanager2.data-layer` contains a `data-layer` argument that could be modified and extended with other XML layout files.

# How to add additional fields to product impressions?
A separate class `\Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper` is used to supply the fields per product impression dynamically. You can create a DI plugin to intercept the return value of the method `mapByProduct()` (by creating a DI plugin method `afterMapByProduct()`): Each entry in the returned array has a key that refers to the data property of the product (EAV attribute) and a value that refers to the Data Layer field (or `impressionField`). 

For example, you could declare a DI plugin within your own `di.xml`, create a DI plugin class and use a method like the following:
```php
public function afterMapByProduct(ProductDataMapper $productDataMapper, array $productData, ProductInterface $product): array 
{
    $productData['foo'] = 'bar';
    return $productData;
}
```

# I'm getting CSP errors with this extension
We have tested this extension with out-of-the-box functionality thoroughly upon CSP errors. And the chance that there are CSP errors
related to this extension itself is small.

Note that non-developers can easily add new third-party scripts to the Google Tag Manager console (so not within Magento, but from
their click-click Google account) which then would cause issues with CSP. This is the goal of CSP: Make sure that third-party
scripts are only allowed if the CSP policy is modified to accommodate them. This CSP concept has zero to do with the Magento
extension though.
