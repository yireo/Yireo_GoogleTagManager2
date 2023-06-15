define(['yireoGoogleTagManagerLogger'], function (logger) {
    return function (eventData, message) {
        window.YIREO_GOOGLETAGMANAGER2_PAST_EVENTS = window.YIREO_GOOGLETAGMANAGER2_PAST_EVENTS || [];

        const copyEventData = Object.assign({}, eventData);
        if (copyEventData.meta) {
            delete copyEventData.meta;
        }

        const eventHash = btoa(JSON.stringify(copyEventData));
        if (window.YIREO_GOOGLETAGMANAGER2_PAST_EVENTS.includes(eventHash)) {
            logger('Warning: Event already triggered', eventData);
            return;
        }

        if (!message) {
            message = 'push (unknown) [unknown]';
        }

        logger(message, eventData);
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ecommerce: null});
        window.dataLayer.push(eventData);
        window.YIREO_GOOGLETAGMANAGER2_PAST_EVENTS.push(eventHash);
    };
});
