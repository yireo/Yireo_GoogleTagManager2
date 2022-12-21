# Does this extension work together with Hyvä?
Yes, this extension ships with native JavaScript code that works with Hyvä. Additionally, the module ships with Hyvä-specific XML layout handles. Note that this applies to the Yireo GoogleTagManager2 version 3. With version 2, you will still need the [compatibility module](https://gitlab.hyva.io/hyva-themes/hyva-compat/magento2-yireo-googletagmanager2/). 

Currently, Luma-based checkouts work without an issue, but for using the React-based checkout or the MageWire-based checkout, more work is needed.

# Does this extension work together with OneStepCheckout?
Yes, this extension works nicely together with [onestepcheckout.com](https://www.onestepcheckout.com/)

# I've installed the module but nothing happens
Make sure that both Google Analytics and Google Tag Manager are properly configured. See our [tutorial](/blob/master/TUTORIAL.md) for additional guidance. Make sure the module settings in Magento are properly configured: The flag **Enabled** sets to be set to **Yes**. The **Container Public ID** needs to start with `GTM-` (unless you are testing in the Developer Mode). Optionally debugging can be enabled, which should print various messages in the **Console** of your browser. Refresh all Magento caches. Within the **Network** tab of your browsers Developer Tools, you should be able to see an outgoing request to `https://www.googletagmanager.com/`.

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

