define([
    'mage/utils/wrapper',
    'yireoGoogleTagManagerLogger'
], function (wrapper, logger, stepNavigator) {
    'use strict';
    return function (stepNavigator) {
        stepNavigator.steps.subscribe(function (steps) {
            if (steps[0].isVisible()) {
                const eventData = window['YIREO_GOOGLETAGMANAGER2_BEGIN_CHECKOUT'];

                if (eventData === null || eventData === undefined) {
                    logger('skipped "begin_checkout" event because data is empty')
                    return;
                }

                logger('push (page event "begin_checkout") [step-navigator-mixin.js]', eventData);
                window.dataLayer.push({ecommerce: null});
                window.dataLayer.push(eventData);
            }
        });
        return stepNavigator;
    }
});
