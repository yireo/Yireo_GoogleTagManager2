define(['jquery'], function ($) {
    'use strict';

    var mixin = {
        submitForm: function (form) {
            const formData = Object.fromEntries(new FormData(form[0]).entries());

            const debug = YIREO_GOOGLETAGMANAGER2_DEBUG || false; // @todo
            const categoryName = ''; // @todo
            const productData = {
                id: formData.product,
                sku: form.data().productSku,
                category: categoryName,
                price: formData.productPrice || null, // @todo
                quantity: formData.product || 1,
            }

            if (debug) {
                console.log('catalog-add-to-cart-mixin', productData);
            }

            dataLayer.push({ecommerce: null});
            dataLayer.push({
                'event': 'addToCart',
                'currencyCode': 'EUR', // @todo
                'ecommerce': {
                    'click': {
                        'actionField': {'list': categoryName},
                        'products': [productData]
                    }
                }
            });

            return this._super(form);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, mixin);
        return $.mage.catalogAddToCart;
    };
});
