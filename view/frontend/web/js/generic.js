/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright (c) 2022 Yireo (https://www.yireo.com/)
 * @license     Open Software License
 */
define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'googleTagManagerLogger',
    'googleTagManagerPush',
    'knockout'
], function ($, _, Component, customerData, logger, pusher, ko) {
    'use strict';

    var moduleConfig = {};

    var isDisabled = function () {
        if (isValidConfig() === false) {
            return true;
        }

        return isAllowedByCookieRestrictionMode() === false;
    };

    var isValidConfig = function () {
        if (typeof moduleConfig.id === 'undefined' || !moduleConfig.id) {
            logger('Warning: Identifier empty, terminating GTM initialization.');
            return false;
        }

        return true;
    };

    var isAllowedByCookieRestrictionMode = function () {
        if (!moduleConfig.cookie_restriction_mode) {
            return true;
        }

        const parsedCookie = JSON.parse($.cookie(moduleConfig.cookie_restriction_mode) || '{}');

        return parsedCookie[moduleConfig.website_id] || false;
    };

    var isLoggedIn = function () {
        var customer = customerData.get('customer');
        return customer() && customer().firstname;
    };

    var processGtmDataFromSection = function (sectionName) {
        const eventData = getGtmDataFromSection(sectionName);
        if (true === isEmpty(eventData)) {
            return;
        }

        pusher(eventData, 'push (customerData "' + sectionName + '" changed) [generic.js]');
    }

    var processGtmEventsFromSection = function (sectionName) {
        const sectionData = customerData.get(sectionName)();
        const gtmEvents = sectionData.gtm_events;

        if (true === isEmpty(gtmEvents)) {
            return;
        }

        for (const [eventId, eventData] of Object.entries(gtmEvents)) {
            processGtmEvent(eventId, eventData, sectionName, sectionData);
        }
    }

    var processGtmEvent = function(eventId, eventData, sectionName, sectionData) {
        logger('customerData section "' + sectionName + '" contains event "' + eventId + '"', eventData);

        const metaData = Object.assign({}, eventData.meta);
        if (metaData && metaData.allowed_events && metaData.allowed_events.length > 0) {
            for (const [allowedEventKey, allowedEvent] of Object.entries(metaData.allowed_events)) {
                $(window).on(allowedEvent, function () {
                    pusher(eventData, 'push (allowed event "' + allowedEventKey + '") [generic.js]');
                });
            }

            return;
        }

        pusher(eventData, 'push (event from customerData "' + sectionName + '") [generic.js]');

        // Make sure that a non-cacheable GTM event is not stored in customerData
        if (!metaData || metaData.cacheable !== true) {
            delete sectionData['gtm_events'][eventId];
            logger('invalidating sections "' + sectionName + '"', sectionData)
            customerData.set(sectionName, sectionData);
        }
    }

    var getGtmDataFromSection = function (sectionName) {
        var sectionData = customerData.get(sectionName);
        var gtmData = sectionData().gtm;
        if (gtmData) {
            return gtmData;
        }

        return {};
    };

    var subscribeToSectionDataChanges = function (sectionName) {
        var sectionData = customerData.get(sectionName);
        sectionData.subscribe(function () {
            processGtmDataFromSection(sectionName);
            processGtmEventsFromSection(sectionName);
        });
    }

    var getSectionNames = function () {
        return ['cart', 'customer', 'gtm-checkout'];
    }

    var isEmpty = function (variable) {
        if (typeof variable === 'undefined') {
            return true;
        }

        if (Array.isArray(variable) && variable.length === 0) {
            return true;
        }

        return typeof variable === 'object' && Object.keys(variable).length === 0;
    }

    return Component.extend({
        initialize: function (config) {
            let attributes = {};
            const sectionNames = getSectionNames();
            sectionNames.forEach(function (sectionName) {
                attributes = $.extend(getGtmDataFromSection(sectionName), attributes);
            });

            logger('initial state (js)');
            window.dataLayer = window.dataLayer || [];

            if (false === isEmpty(attributes)) {
                pusher(attributes, 'push (attributes) [generic.js]');
            }

            sectionNames.forEach(function (sectionName) {
                processGtmEventsFromSection(sectionName);
                subscribeToSectionDataChanges(sectionName);
            });
        }
    });
});
