# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.9.8] - 12 September 2024
### Fixed
- Fix Uncaught TypeError: products.forEach is not a function #246 @nahall
- Allow configuring which order states should lead to `purchase` event

## [3.9.7] - 29 August 2024
### Fixed
- Only clean dataLayer.ecommerce, if new push actually contains ecommerce data

## [3.9.6] - 23 August 2024
### Fixed
- Move CSP logic to external module `Yireo_CspUtilities`

## [3.9.5] - 22 August 2024
### Fixed
- Revert back to hard-coded proxy #242

## [3.9.4] - 14 August 2024
### Fixed
- Guarantee `subtotal` is always float

## [3.9.3] - 2 August 2024
### Fixed
- Prevent `add_to_cart` from firing twice #236 @henk-hypershop @MaximGns
- Changed reported value from `grand_total` to `subtotal` in various events #240
- Use current category as `item_list_name` parameter #234 @marcinfr
- Only trigger `purchase` event when order is not canceled #227

## [3.9.2] - 4 July 2024
### Fixed
- Extend from SecureHtmlRenderer\Proxy to delay the instantiation #239 @hostep

## [3.9.1] - 1 July 2024
### Fixed
- Type Error in class AddCspInlineScripts with the Hyva Checkout #237

## [3.9.0] - 20 June 2024
### Added
- Added $secureRenderer, so CSP compliance #231 @jemoon @hostep @nige-one
- Added compatibility with older versions without SecureHtmlRenderer class

## [3.8.3] - 10 April 2024
### Fixed
- Remove translation with empty strings #228

## [3.8.2] - 6 April 2024
### Fixed
- Added customer address data to enhanced conversions #226 @JuulGr

## [3.8.1] - 22 March 2024
### Fixed
- Remove page layout directive from Hyva checkout
- Compat Magento latest with psr/log

## [3.8.0] - 15 March 2024
### Added
- Lazyload non-event (non-GA4) data from localStorage in Hyva #213

### Fixed
- Make sure `value` of event `purchase` is without tax and shipping

## [3.7.13] - 15 March 2024
### Fixed
- Require array return type for merge tags
- Create separate EnhancedConversions tag class
- Implement `ArgumentInterface` for Magewire dummy component class @wahidnory #223

## [3.7.12] - 27 February 2024
### Fixed
- Add return to TriggerViewSearchResultDataLayerEvent plugin #214
- `hyva_checkout_index_index.xml` is loaded without having the hyva checkout enabled #218
- Fix restriction cookies with multiple website IDs #217 @jemoon

## [3.7.11] - 6 February 2024
### Fixed
- Fix PHP errors

## [3.7.10] - 6 February 2024
### Fixed
- Better error handling in CategoryProvider #209 @thomas-kl1
- Strange issue with empty config causing issues #210 @shwetawawale

## [3.7.9] - 5 February 2024
### Fixed
- Fix integration test
- Fix potential issues with products only in Root Category

## [3.7.8] - 3 February 2024
### Fixed
- Fix warnings when a product has no categories

## [3.7.7] - 1 February 2024
### Fixed
- Fix DI compilation due to 3.7.6

## [3.7.6] - 1 February 2024
### Fixed
- Refactor loading performance of product data and category data in cart

## [3.7.5] - 27 January 2024
### Fixed
- Make sure that `customer` section in localstorage is refreshed with `wishlist/index/add`

## [3.7.4] - 15 December 2023
### Fixed
- Fix PHP 7.4 compat issues

## [3.7.3] - 15 December 2023
### Fixed
- Livewire: Multiple root elements detected

## [3.7.2] - 13 December 2023
### Fixed
- Improve loading of shipping method (so `add_shipping_info` event)

## [3.7.1] - 12 December 2023
### Fixed
- Fix error when removing cart item #201

## [3.7.0] - 7 December 2023
### Added
- Hyva Checkout support (@hans-vereyken)

## [3.6.5] - 28 November 2023
### Fixed
- Make sure to use `gtm-checkout` in the checkout, not customerData `cart`

## [3.6.4] - 10 October 2023
### Fixed
- Prevent addShippingInfo from triggering an error in integration API #195 @rhoerr

## [3.6.3] - 27 September 2023
### Fixed
- Fix login event fired on every pages Hyva #193
- Fix inconsistent price/value formatting #187
- Allowed pages are not always picked up properly #182 (@gaeldelmer)

## [3.6.2] - 27 September 2023
### Fixed
- Properly implement `allowed_pages` in Hyva scripts
- Make sure `minicart_collapse` is only there if cart event is allowed everywhere
- `Fatal error: Uncaught Error: Call to a member function getCarrierCode` (#192 @WGenie @bvmax)

## [3.6.1] - 21 September 2023
### Fixed
- `currency` should never be part of an `item` #189
- Do not load category products if disabled #190 (@sprankhub)

## [3.6.0] - 6 September 2023
### Added
- New event `sign_up` #185 (@samicoman)

### Fixed
- Fix position of value and currency in `add_to_cart` #186 (@anvanza)

## [3.5.8] - 9 August 2023
### Fixed
- Use correct ID reference with product clicks on PLP pages

## [3.5.7] - 22 July 2023
### Fixed
- Again prevent JS error `Cannot read properties of undefined` under Hyva #181

## [3.5.6] - 21 July 2023
### Fixed
- Prevent JS error `Cannot read properties of undefined` under Hyva #181

## [3.5.5] - 21 July 2023
### Fixed
- Make sure that shipment code is added, if missing in address
- Doublecheck for empty events
- Dont bother adding an event if it is empty

## [3.5.4] - 17 July 2023
### Fixed
- Argument name `productPath` should be `product_path` #179 @meminuygur

## [3.5.3] - 4 July 2023
### Fixed
- JS error flooding error console (#178 @WouterSteen)

## [3.5.2] - 4 July 2023
### Fixed
- `DataLayer/Tag/EnhancedConversions/Sha256EmailAddress.php` breaks in PHP 7.4 @koentjeh

## [3.5.1] - 4 July 2023
### Fixed
- Make sure undefined `YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS` does not cause issues

## [3.5.0] - 4 July 2023
### Fixed
- Automagically add an index to every product being mapped #169

### Added
- Support For Enhanced Conversions (@sprankhub)

## [3.4.5] - 27 June 2023
### Fixed
- Fix issue with uninitialized `eventData` caused by 3.4.4

## [3.4.4] - 26 June 2023
### Fixed
- Fixed stepNavigator issue in non-default checkout

## [3.4.3] - 22 June 2023
### Fixed
- Mixins are still active even when enabled = false

## [3.4.2] - 21 June 2023
### Fixed
- Prevent add-to-cart to trigger view-cart, when view-cart is on cart-page only
- Missing Quantity Parameter in `add_to_cart` Event

## [3.4.1] - 20 June 2023
### Fixed
- Non-visible child products in bundle cause Fatal Exception

## [3.4.0] - 19 June 2023
### Added
- Option to delay loading GTM until user interaction is triggered

## [3.3.4] - 16 June 2023
### Fixed
- Fix issue with JS `btoa` and non-Latin1 characters #162 @amjadm61 

## [3.3.3] - 16 June 2023
### Fixed
- Emergency fix for `number_format` (3 args instead of 4) under PHP 7.4

## [3.3.2] - 16 June 2023
### Fixed
- Guarantee that cart value is always returned with 4 decimals
- Prevent duplicate events with same data
- Make sure to return data if categories cause exception #160
- Make sure non-cacheable events are wiped from Hyva localstorage
- Don't add categories that are added to another Root Category

## [3.3.1] - 15 June 2023
### Fixed
- `parent()` might cause issues with nesting HTML #157 @koentjeh
- Only point `item_id` towards product ID and add a new `order_item_id`
- Make logging with colors more consistant
- Rename `purchase` event to `purchase_event` to comply to module standard
- Combine notice log with push log
- Add filename for better debugging
- #158 fix payment trigger for guest user #159 @koentjeh

## [3.3.0] - 12 June 2023
### Added
- Add `view_search_result` event #156 @gaeldelmer

## [3.2.6] - 7 June 2023
### Fixed
- Make sure no-such-entity-exception doesnt break production
- Make sure to filter categories by `entity_id` AND `is_active`

## [3.2.5] - 6 June 2023
### Fixed
- Make sure `quantity` is a `float` not an `int`
- Alt approach for bypassing non-active categories

## [3.2.4] - 6 June 2023
### Added
- New `etc/data_layer.xml` to extend on global level (instead of using XML layout)

### Fixed
- Categories of product should be enabled to be displayed

## [3.2.3] - 26 May 2023
### Fixed
- Rewrite splat into `array_merge` because of PHP 7.4 (#152)
- Add log to `CustomerSessionDataProvider` (@gaeldelmer)
- Execute `begin_checkout` event using checkout step navigator (@koentjeh #148)

## [3.2.2] - 18 May 2023
### Fixed
- Triggering shipping and payment events on the spot, instead of delayed
- Adding customData section `gtm-checkout` for reloading things in checkout

## [3.2.1] - 10 May 2023
### Added
- Option for custom URL for `gtm.js` icw server-side analytics (@WouterSteen)

## [3.2.0] - 20 April 2023
### Added
- Option to trigger `view_cart` event only when expanding minicart

### Fixed
- Support any method in any entity with `GetAttributeValue`
- GA4 container ID client-side validation #142 @koentjeh

## [3.1.3] - 8 April 2023
### Fixed
- Fixed default `product_path` config for product clicks

## [3.1.2] - 8 April 2023
### Added
- Option to only generate `view_cart` on the cart page
- Move product clicks to separate template and make `productPath` configurable through layout

## [3.1.1] - 31 March 2023
### Fixed
- Product category information in data layer is incomplete #138 @DuckThom
- Return price in the current currency for multi-currency stores #127 @samicoman
- Reset ecommerce `dataLayer` everywhere
- Use `window.dataLayer` instead of `dataLayer`

### Added
- Add currency and value to `view_cart` event

## [3.1.0] - 19 March 2023
### Fixed
- Fix undeclared `$scopeConfig` in PHP 8.2

### Added
- Add additional `magento_sku` and `magento_id` to products

## [3.0.18] - 17 March 2023
### Fixed
- Fix Invisible Cart Items #131 (@stijntrinos)

## [3.0.17] - 14 March 2023
### Fixed
- Better fix for product edit pages

## [3.0.16] - 13 March 2023
### Fixed
- Cast prices to float in orders and order items #133 (@lfolco)
- Also support product edit pages
- Optimise GetProductsFromCategoryBlockPlugin #135

## [3.0.15] - 7 March 2023
### Fixed
- Prevent unknown `$html` argument in plugin to cause Fatal Error, not reported here, not duplicated 

## [3.0.14] - 28 February 2023
### Fixed
- Use tax config for prices in cart/order events #129 (@samicoman)
- Return prices as numbers instead of strings #128 (@samicoman)

## [3.0.13] - 21 February 2023
### Fixed
- Make sure array does not get converted to string #125
- Removed duplicate IFRAME #126 (@elioermini)

## [3.0.12] - 14 February 2023
### Fixed
- Use `Magento\Checkout\Model\Cart` instead of `CartInterface` #122

## [3.0.11] - 14 February 2023
### Fixed
- Use core method instead of custom filter #123 (@sprankhub)
- Prevent duplication of items in the `view_item_list` event #119 (@samicoman)

## [3.0.10] - 31 January 2023
### Fixed
- Prevent TypeError with final price #120

## [3.0.9] - 20 January 2023
### Fixed
- Fix purchase event
- Prevent duplication of items in the purchase event when configurable product were ordered (@samicoman)

## [3.0.8] - 16 January 2023
### Fixed
- Prevent failure when no eav attribute set in the config #117 (@samicoman)

## [3.0.7] - 13 January 2023
### Fixed
- Use `sku` instead of `id` as `item_id` #114 (@samicoman)
- Properly format product price #114 (@samicoman)
- Add categories per product #114 (@samicoman)
- Make sure to calculate value with right price in cart events #115 (@samicoman)
- Handle configurable products in purchase event #116 (@samicoman)

## [3.0.6] - 31 December 2022
### Fixed
- Prevent reloading order per order-item

## [3.0.5] - 21 December 2022
### Fixed
- Render debugging block only if available

## [3.0.4] - 17 December 2022
### Fixed
- Finalize product clicks for Hyva
- Fix typo in payment validator

## [3.0.3] - 13 December 2022
### Fixed
- Hyva event handling

## [3.0.2] - 13 December 2022
### Fixed
- Basic compatibility with Hyva

## [3.0.1] - 13 December 2022
### Fixed
- Backend model prevents saving container ID

## [3.0.0] - 12 December 2022
### Added
- GA4 support
- Removed GA4/UA/EE support
- Extensibility via XML layout
- Support for EAV attributes of products and categories
- Click events
- Cart & checkout events 
- Integration Tests

## [2.1.8] - 1 July 2022
### Fixed
- Update ecommerce attributes #95 (@BorisovskiP)

## [2.1.7] - 31 March 2022
### Fixed
- Prevent adding quote data to success page #91
- Add additional field for ecommerce push #93 (@sprankhub)

### Fixed
- Prevent adding quote data to success page #91
- Add additional field for ecommerce push #93 (@sprankhub)

## [2.1.7] - 31 March 2022
### Fixed
- Prevent adding quote data to success page #91
- Add additional field for ecommerce push #93 (@sprankhub)

## [2.1.6] - 23 March 2022
### Fixed
- Add ecommerce push on checkout success #90 (@sprankhub)

## [2.1.5] - 21 March 2022
### Fixed
- Fix Duplicate Product Tracking #88 (@sprankhub)
- Add parent SKU #89 (@sprankhub)

## [2.1.4] - 17 February 2022
### Fixed
Fix duplicate product loading #85 (@sprankhub)


## [2.1.3] - 23 September 2021
### Fixed
- Prevent Fatal Error with `Amasty_Xlanding`

## [2.1.2] - 29 July 2021
### Fixed
- Fix error on cart configure page (@Irinina)

## [2.1.1] - 10 July 2021
### Fixed
- Make sure \Yireo\GoogleTagManager2\ViewModel\Product::getProductPrice() always returns float

## [2.1.0] - 10 July 2021
### Fixed
- Various styling issues (PHPCS, PHPStan)
- Increase framework requirement to 101.2 because of ViewModel bug

### Added
- Enabled debugging in JS (dumping attributes and config)
- New Attributes ViewModel to collect data after initialize output
- Add API interfaces to guarantee backwards compatibility in future
- Added XML layout container `gooogletagmanager_container` for most datalayer blocks
- Added debug utility class
- Renamed existing plugin interceptors

### Removed
- Rewrite x-magento-init into simple require() for performance
- Refactored `Category` block to no longer depend on `onLoadedProductCollection`
- Removed all block classes (`Script`, `Category`, `Product`, `Generic`, `Custom`)
- Remove `\Yireo\GoogleTagManager2\Util\GetCurrentProduct`
- Remove `\Yireo\GoogleTagManager2\Util\GetCurrentCategory`
- Remove helper
- Remove container model
- Remove entire observer-based input method
- Remove ViewModelFactory

## [2.0.5] - 5 May 2021
### Fixed
Re-add CSP whitelisting

## [2.0.4] - 30 April 2021
### Fixed
- Fix block retrieval with Layout instead LayoutFactory (@sprankhub)
- Make sure view model is set correctly (@sprankhub)

## [2.0.3] - 29 October 2020
### Fixed
- Fix error when block is not present

## [2.0.2] - 28 October 2020
### Fixed
- Category Sort By not working properly with 2.4.X because of weird product loading (70)
- Refactored legacy Registry into request
- Move Config class to new namespace
- PHPCS fixes for Magento Marketplace compliance

## [2.0.1] - 29 July 2020
### Added
- Magento 2.4 support

## [2.0.0] - 2020-07-21
### Removed
- Legacy CustomerData class
- Dev dependency with Mockery

### Fixed
- Upgrade PHPUnit to be 2.4 compatible
- Bumped minimum PHP to 7.2

## [1.1.3] - 2020-05-30
### Fixed
- Add a new CSP whitelist for M2.3.5+

## [1.1.2] - 2020-03-31
### Fixed
- Some small code quality things

## [1.1.1] - 2020-02-10
### Added
- Add ACL file for configuring access to config

## [1.1.0] - 2019-11-23
### Added
- Major rewrite to remove custom sections in favor of DI plugin
- No more reloading of sections

## [1.0.3] - 2019-08-07
### Fixed
- Use currency code instead of currency symbol (@nicolas-medina)

## [1.0.2] - 2019-06-15
### Fixed
- Move cookie-restriction-mode to JS to work with FPC

## [1.0.1] - 2019-03-13
### Fixed
- Fix duplicate code
- Add compatibility with Magento 2.2 again (2.1 is dropped permanently)
- Fix invalid template path
- Fix reloading issues with quote

## [1.0.0] - 2019-02-26
### Fixed
- First release with Changelog
- See GitHub commits for earlier messages
