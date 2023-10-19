define([
    'mage/utils/wrapper',
    'googleTagManagerPush',
    'googleTagManagerLogger',
], function (wrapper, pusher, logger, stepNavigator) {
    'use strict';
    return function (stepNavigator) {
        const enabled = window.AdPage_GTM_ENABLED;
        if (enabled === null || enabled === undefined) {
            return stepNavigator;
        }

        stepNavigator.steps.subscribe(function (steps) {
            const firstStep = steps[0];
            const eventData = window.AdPage_GTM_BEGIN_CHECKOUT;

            if (firstStep === undefined || firstStep == null || firstStep.length <= 0) {
                logger('Error: No steps detected. Triggering event anyway :o')
                pusher(eventData, 'push (page event "begin_checkout") [step-navigator-mixin.js]');
                return;
            }

            if (!firstStep.isVisible()) {
                return;
            }
            
            if (eventData === null || eventData === undefined) {
                logger('skipped "begin_checkout" event because data is empty')
                return;
            }

            pusher(eventData, 'push (page event "begin_checkout") [step-navigator-mixin.js]');
        });

        return stepNavigator;
    }
});
