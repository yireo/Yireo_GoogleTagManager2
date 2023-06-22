define([
    'mage/utils/wrapper',
    'yireoGoogleTagManagerPush',
    'yireoGoogleTagManagerLogger',
], function (wrapper, pusher, logger, stepNavigator) {
    'use strict';
    return function (stepNavigator) {
        const enabled = window.YIREO_GOOGLETAGMANAGER2_ENABLED;
        if (enabled === null || enabled === undefined) {
            return stepNavigator;
        }

        stepNavigator.steps.subscribe(function (steps) {
            if (steps[0].isVisible()) {
                const eventData = window.YIREO_GOOGLETAGMANAGER2_BEGIN_CHECKOUT;

                if (eventData === null || eventData === undefined) {
                    logger('skipped "begin_checkout" event because data is empty')
                    return;
                }

                pusher(eventData, 'push (page event "begin_checkout") [step-navigator-mixin.js]');
            }
        });

        return stepNavigator;
    }
});
