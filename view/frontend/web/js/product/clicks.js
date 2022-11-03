define([
    'jquery',
    'yireoGoogleTagManagerLogger'
], function($, logger) {
    $('.product-items a.product').click(function(event) {
        const debugClicks = YIREO_GOOGLETAGMANAGER2_DEBUG_CLICKS || false;
        const $parent = $(this).parent();
        const regex = /_(\d+)$/;
        const matches = $parent.attr('id').match(regex);
        const productId = matches[1];

        const gtmData = {
            'event': 'select_item',
            'ecommerce': {
                'items': [{
                    id: productId
                }]
            }
        }

        logger('page event "select_item" (js)', gtmData);
        dataLayer.push({ ecommerce: null });
        dataLayer.push(gtmData);

        if (debugClicks && confirm("Press to continue with redirect") === false) {
            event.preventDefault();
        }
    });
});
