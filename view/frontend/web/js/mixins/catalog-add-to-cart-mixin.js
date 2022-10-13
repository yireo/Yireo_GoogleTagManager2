define(['jquery'], function ($) {
    'use strict';

    var mixin = {
        submitForm: function (form) {
            const formData = Object.fromEntries(new FormData(form[0]).entries());

            const debug = YIREO_GOOGLETAGMANAGER2_DEBUG || false; // @todo
            const categoryName = ''; // @todo
            const productData = {
                item_id: formData.product,
                item_sku: form.data().productSku,
                item_category: categoryName,
                price: formData.productPrice || null, // @todo
                quantity: formData.product || 1,
            }

            if (debug) {
                console.log('Yireo_GoogleTagManager2: catalog-add-to-cart-mixin', productData);
            }

            dataLayer.push({ecommerce: null});
            dataLayer.push({
                'event': 'add_to_cart',
                'currencyCode': 'EUR', // @todo
                'ecommerce': {
                    'items': [productData]
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
