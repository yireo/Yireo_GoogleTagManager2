define([
    'jquery',
    'googleTagManagerPush'
], function($, pusher) {
    return function(config, element) {
        const productPath = config.productPath || '.product-items a.product';
        $(productPath).click(function(event) {
            const debugClicks = window['AdPage_GTM_DEBUG_CLICKS'] || false;
            const $parent = $(this).closest('[id^=product-item-info_]');
            const regex = /_(\d+)$/;
            const matches = $parent.attr('id').match(regex);
            const productId = matches[1];
            const productData = window['AdPage_GTM_PRODUCT_DATA_ID_' + productId] || {};
            productData.item_id = productId;

            const eventData = {
                'event': 'select_item',
                'ecommerce': {
                    'items': [productData]
                }
            }

            pusher(eventData, 'push (page event "select_item") [clicks.js]');

            if (debugClicks && confirm("Press to continue with redirect") === false) {
                event.preventDefault();
            }
        });
    }
});
