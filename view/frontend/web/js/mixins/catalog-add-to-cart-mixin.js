define([
    'jquery',
    'googleTagManagerPush'
], function ($, pusher) {
    'use strict';

    const enabled = window.Tagging_GTM_ENABLED;
    if (enabled === null || enabled === undefined) {
        return function (targetWidget) {
            return $.mage.catalogAddToCart;
        };
    }

    var mixin = {
        submitForm: function (form) {
            const formData = Object.fromEntries(new FormData(form[0]).entries());
            const productId = formData.product;
            const productData = window['Tagging_GTM_PRODUCT_DATA_ID_' + productId] || {};
            productData.quantity = formData.qty || 1;

            const eventData = {
                'event': 'trytagging_add_to_cart',
                'ecommerce': {
                    'items': [productData]
                }
            };

            pusher(eventData, 'push [catalog-add-to-cart-mixin.js]');
            return this._super(form);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, mixin);
        return $.mage.catalogAddToCart;
    };
});
