define(['googleTagManagerLogger'], function (logger) {
    return function (eventData, message) {
        window.AdPage_GTM_PAST_EVENTS = window.AdPage_GTM_PAST_EVENTS || [];

        const metaData = Object.assign({}, eventData.meta);

        const cleanEventData = Object.assign({}, eventData);
        if (cleanEventData.meta) {
            delete cleanEventData.meta;
        }

        if (cleanEventData.length === 0) {
            return;
        }

        if (metaData && metaData.allowed_pages && metaData.allowed_pages.length > 0
            && false === metaData.allowed_pages.some(page => window.location.pathname.includes(page))) {
            logger('Warning: Skipping event, not in allowed pages', window.location.pathname, eventData);
            return;
        }

        // Prevent the same event from being triggered twice, when containing the same data
        const eventHash = btoa(encodeURIComponent(JSON.stringify(cleanEventData)));
        if (window.AdPage_GTM_PAST_EVENTS.includes(eventHash)) {
            logger('Warning: Event already triggered', eventData);
            return;
        }

        if (!message) {
            message = 'push (unknown) [unknown]';
        }

        logger(message, eventData);
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ecommerce: null});
        window.dataLayer.push(cleanEventData);
        window.AdPage_GTM_PAST_EVENTS.push(eventHash);
    };
});
