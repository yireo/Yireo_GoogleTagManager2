define(['jquery'], function ($) {
    'use strict';

    var mixin = {
        submitForm: function (form) {
            const formData = Object.fromEntries(new FormData(form[0]).entries());
            console.log('formdata', formData, form);
            const productId = formData.product;
            const productSku = form.data().productSku;

            let debug = false;
            if (typeof YIREO_GOOGLETAGMANAGER2_DEBUG !== 'undefined') {
                debug = YIREO_GOOGLETAGMANAGER2_DEBUG;
            }

            let debugClicks = false;
            if (typeof YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS !== 'undefined') {
                debugClicks = YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS;
            }

            let productPrice = '';
            let productCurrency = '';
            let productCategory = '';

            let $productSkuBox = $('[data-product-sku="' + productSku + '"]');
            let $productBox = $productSkuBox.closest('.product-info-main');
            console.log('Product Box 1', $productBox);
            if (!$productBox.length) {
                $productBox = $productSkuBox.closest('.product-item-inner');
                console.log('Product Box 2', $productBox);
            }

            if ($productBox.length) {
                productPrice = $productBox.find('[data-product-price-amount]').first().attr('data-product-price-amount');
                productCurrency = $productBox.find('[data-product-price-currency]').first().attr('data-product-price-currency');
                productCategory = $productBox.find('[data-product-category]').first().attr('data-product-category');
            }

            const productData = {
                item_id: productId,
                item_sku: form.data().productSku,
                item_category: productCategory,
                price: productPrice,
                quantity: formData.qty || 1,
            }

            const eventData = {
                'event': 'add_to_cart',
                'currencyCode': productCurrency,
                'ecommerce': {
                    'items': [productData]
                }
            };

            if (debug) {
                console.log('Yireo_GoogleTagManager2: catalog-add-to-cart-mixin event', eventData);
                console.log('Yireo_GoogleTagManager2: catalog-add-to-cart-mixin productData', productData);
            }

            if (debugClicks && confirm("Press to continue with add-to-cart") === false) {
                return;
            }

            dataLayer.push({ecommerce: null});
            dataLayer.push(eventData);
            return this._super(form);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, mixin);
        return $.mage.catalogAddToCart;
    };
});
