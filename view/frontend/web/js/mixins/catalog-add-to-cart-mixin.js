define([
    'jquery',
    'googleTagManagerPush'
], function ($, pusher) {
    'use strict';

    const enabled = window.AdPage_GTM_ENABLED;
    if (enabled === null || enabled === undefined) {
        return function (targetWidget) {
            return $.mage.catalogAddToCart;
        };
    }

    var mixin = {
        submitForm: function (form) {
            const formData = Object.fromEntries(new FormData(form[0]).entries());
            const productId = formData.product;

            const debugClicks = window['AdPage_GTM_DEBUG_CLICKS'] || false;
            const productData = window['AdPage_GTM_PRODUCT_DATA_ID_' + productId] || {};
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

            pusher(eventData, 'push [catalog-add-to-cart-mixin.js]');
            return this._super(form);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, mixin);
        return $.mage.catalogAddToCart;
    };
});
