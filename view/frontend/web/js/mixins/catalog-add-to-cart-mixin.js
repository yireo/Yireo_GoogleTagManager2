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

            logger('catalog-add-to-cart-mixin event', eventData);
            logger('catalog-add-to-cart-mixin productData', productData);

            if (debugClicks && confirm("Press to continue with add-to-cart") === false) {
                return;
            }

            dataLayer.push(eventData);
            return this._super(form);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, mixin);
        return $.mage.catalogAddToCart;
    };
});
