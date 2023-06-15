define([
    'jquery',
    'yireoGoogleTagManagerLogger'
], function ($, logger) {
    'use strict';

    var mixin = {
        submitForm: function (form) {
            const formData = Object.fromEntries(new FormData(form[0]).entries());
            const productId = formData.product;

            let debugClicks = false;
            if (typeof YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS !== 'undefined') {
                debugClicks = YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS;
            }

            const productData = window['YIREO_GOOGLETAGMANAGER2_PRODUCT_DATA_ID_' + productId] || {};
            productData.quantity = formData.qty || 1;

            const eventData = {
                'event': 'add_to_cart',
                'ecommerce': {
                    'items': [productData]
                }
            };

            if (debugClicks && confirm("Press to continue with add-to-cart") === false) {
                return;
            }

            logger('push [catalog-add-to-cart-mixin.js]', eventData);
            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(eventData);
            return this._super(form);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, mixin);
        return $.mage.catalogAddToCart;
    };
});
