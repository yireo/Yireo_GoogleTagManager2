# Is this extension supporting Enhanced E-commerce?
Yes, it is. However, if features are lacking, feel free to collaborate on this by creating a Pull Request, if you feel like it.

# Does this extension work together with OneStepCheckout?
Yes, this extension works nicely together with [onestepcheckout.com](https://www.onestepcheckout.com/)

# How to extend upon the Data Layer created by this module?
The main methodology for this module to generate its Data Layer by using the XML layout. The block with name `yireo_googletagmanager2.data-layer` contains a `data-layer` argument that could be modified and extended with other XML layout files.

# How to add additional fields to product impressions?
A separate class `\Yireo\GoogleTagManager2\DataLayer\Mapper\ProductDataMapper` is used to supply the fields per product impression dynamically. You can create a DI plugin to intercept the return value of the method `getProductFields()` (by creating a DI plugin method `afterGetProductFields()`): Each entry in the returned array has a key that refers to the data property of the product (EAV attribute) and a value that refers to the Data Layer field (or `impressionField`). 

For example, if your products use an EAV attribute `manufactorer` and if you want to map this color value to a Data Layer field `brand`, you could declare a DI plugin within your own `di.xml`, create a DI plugin class and use a method like the following:
```php
public function afterGetProductFields(ProductDataMapper $productDataMapper, array $productFields): array 
{
    $productFields['manufactorer'] = 'brand';
    return $productFields;
}
```
