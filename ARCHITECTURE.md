# Yireo GoogleTagManager2 architecture

Note that this document only applies to version 3.0 and higher.

## Extensibility in general
- XML layout arguments
- Injectable interfaces in namespace `Yireo\GoogleTagManager2\Api\`
- Interfaces to enforce certain behaviour (like `TagInterface`)

Don't touch anything else. If you want to add something in a way that is not supported, open a GitHub Issue and let's discuss how to support it anyway.

## DataLayer definition
The datalayer that is sent to Google Tag Manager is in general built up in two ways: Via inline scripts that are inserted into the HTML of pages and via external JavaScript files. The inline scripts are based on a ViewModel `Yireo\GoogleTagManager2\ViewModel\DataLayer` that is added (under the argument `data_layer_view_model`) to a block named `yireo_googletagmanager2.data-layer`. The block also adds two other arguments: `data_layer` and `data_layer_processors`. Both arguments are used by the ViewModel to build up the datalayer. The argument `data_layer` contains a static definition, derived from XML layout. The argument `data_layer_processors` contains a listing of PHP processors that could modify the static `data_layer` afterwards.

## XML layout argument `data_layer`
The XML layout argument `data_layer` is used to generate the initial datalayer (as part of the HTML document) and it should contain therefore only values (or tags) that are cacheable. In other words, values related to customer sessions or checkout sessions should be picked up in JavaScript instead.

The `data_layer` in the XML layout is an array of items, where each item could be of various types `xsi:type`: Strings, numeric values, booleans. Whatever ends up in this array is converted into JSON that is pushed as the first datalayer. See the file `view/frontend/layout/default.xml` for a jumpstart.

Thanks to the fact that the XML layout is extensible by modules and themes, this datalayer of the Yireo GoogleTagManager2 module is fully extensible as well. This is shown in various additional handles, as part of this module:

## Tag classes
A tag `version` could be added to the datalayer with an XML layout statement like the following:
```xml
<argument name="data_layer" xsi:type="array">
    <item name="version" xsi:type="string">0.1.2</item>
</argument>
```

If the `xsi:type` is `object`, then the value could refer to a class. A restriction within the Yireo GoogleTagManager2 is that this class implements `Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface`:

```xml
<argument name="data_layer" xsi:type="array">
    <item name="version" xsi:type="object">Yireo\GoogleTagManager2\DataLayer\Tag\Version</item>
</argument>
```

In the example above, the tag class `Yireo\GoogleTagManager2\DataLayer\Tag\Version` implements the interface `Yireo\GoogleTagManager2\DataLayer\Tag\TagInterface` and it returns a simple string (so, the version). This string is then added as `version` into the datalayer. In other words, the item name in the XML layout determines the name of the datalayer property.

The namespace `Yireo\GoogleTagManager2\DataLayer\Tag` contains all tag classes, offered out-of-the-box. If you want to add more values, you can add them through the XML layout. You can do this through a theme. If you also want to add your own custom tag class, create a module instead, make it dependent on the `Yireo_GoogleTagManager2` module (both in your `composer.json` file and your `etc/module.xml` file) and use the tag interfaces as mentioned.

## Data layer processors
On top of the layout-based datalayer approach, which is already quite flexible, you can also parse entries via data layer processors as well: Classes that are defined through the XML layout argument `data_layer_processors` of the main block and are implementing `\Yireo\GoogleTagManager2\DataLayerProcessor\ProcessorInterface`: 

```xml
<referenceBlock name="yireo_googletagmanager2.data-layer">
    <arguments>
        <argument name="data_layer_processors" xsi:type="array">
            <item name="category" xsi:type="object">Yireo\GoogleTagManager2\DataLayerProcessor\Category</item>
        </argument>
    </arguments>
</referenceBlock>
```

The recommendation is to use this as a last resort. And again, make sure to realize that this approach results in a datalayer that should be cacheable: No private content is allowed!

## Main JavaScript file and `customerData`
On every page, an additional JavaScript file `Yireo_GoogleTagManager2::js/generic.js` is loaded to dynamically add tags that are private, like checkout-related information and customer-specific details. The logic here relies upon the Magento `customerData` logic that provides information to JavaScript via an AJAX call to the URL `customer/section/load` (which returns JSON) which is then cached in `localStorage`. 

The `customerData` sections `cart` and `customer` are extended with DI plugin interceptors to add a subarray `gtm` which is then picked up in JavaScript (see `etc/frontend/di.xml`). If you want to add more data than provided by default, either create your own DI plugin interceptors in your own module. Or use one of the session data providers elsewhere in your code (like an observer).

The two session data providers - `\Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface` and `\Yireo\GoogleTagManager2\Api\CustomerSessionDataProviderInterface` - only serve one purpose: To temporarily hold information that is added to the DI plugin interceptors for the corresponding `customerData` sections. They contain an easy method like `append()`. If you would use `append(['foo' => 'bar'])`, then the DI plugin will add a tag `foo` with value `bar` to the datalayer.

## JavaScript mixins for datalayer events
A RequireJS mixin is added to `Magento_Catalog/js/catalog-add-to-cart` to trigger the datalayer event `addToCart`.

## Observer for `removeFromCart` event
The datalayer event `removeFromCart` is ideally triggered from JavaScript as well, but unfortunately the JavaScript API is not consistent. Instead, the event `sales_quote_remove_item` is observed by an observer which calls upon the session data provider `\Yireo\GoogleTagManager2\Api\CheckoutSessionDataProviderInterface` to temporarily add the event data to the session and then append this to the `customerData` section. Right.
