define([
    'mage/utils/wrapper',
    'yireoGoogleTagManagerLogger'
], function (wrapper, logger, stepNavigator) {
    'use strict';
    return function (stepNavigator) {
        stepNavigator.steps.subscribe(function (steps) {
            if (steps[0].isVisible()) {
                const gtmData = window['YIREO_GOOGLETAGMANAGER2_BEGIN_CHECKOUT'];

                if (gtmData === null || gtmData === undefined) {
                    logger('skipped "begin_checkout" event because data is empty')
                    return;
                }

                logger('page event "begin_checkout" (js)', gtmData);
                window.dataLayer.push({ecommerce: null});
                window.dataLayer.push(gtmData);
            }
        });
        return stepNavigator;
    }
});
