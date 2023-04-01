define([
    'jquery',
    'yireoGoogleTagManagerLogger'
], function($, logger) {
    return function(config, element) {
        const productPath = config.productPath || '.product-items a.product';
        $(productPath).click(function(event) {
            const debugClicks = YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS || false;
            const $parent = $(this).parent();
            const regex = /_(\d+)$/;
            const matches = $parent.attr('id').match(regex);
            const productId = matches[1];
            const productData = window['YIREO_GOOGLETAGMANAGER2_PRODUCT_DATA_ID_' + productId] || {};
            productData.item_id = productId;

            const gtmData = {
                'event': 'select_item',
                'ecommerce': {
                    'items': [productData]
                }
            }

            logger('page event "select_item" (js)', gtmData);
            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(gtmData);

            if (debugClicks && confirm("Press to continue with redirect") === false) {
                event.preventDefault();
            }
        });
    }
});
