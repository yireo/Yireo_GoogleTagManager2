/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright (c) 2017 Yireo (https://www.yireo.com/)
 * @license     Open Software License
 */

var deps = [];

if (typeof window.checkoutConfig !== 'undefined') {
    deps.push('Magento_Checkout/js/model/quote');
}

define(deps, function (quote) {
    return {
        isQuoteAvailable: function () {
            return typeof quote !== 'undefined';
        },

        getQuote: function () {
            return quote;
        }
    };
});