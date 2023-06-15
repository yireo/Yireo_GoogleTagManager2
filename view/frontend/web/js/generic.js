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
    'yireoGoogleTagManagerLogger',
    'knockout'
], function ($, _, Component, customerData, logger, ko) {
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

        return $.cookie(moduleConfig.cookie_restriction_mode);
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

        logger('push (customerData "' + sectionName + '" changed) [generic.js]', eventData);
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push(eventData);
    }

    var processGtmEventsFromSection = function (sectionName) {
        const sectionData = customerData.get(sectionName)();
        const gtmEvents = sectionData.gtm_events;

        if (true === isEmpty(gtmEvents)) {
            return;
        }

        for (const [eventId, eventData] of Object.entries(gtmEvents)) {
            if (eventData.triggered === true) {
                continue;
            }

            const metaData = eventData.meta;
            if (metaData) {
                delete eventData.meta;
            }

            logger('customerData section "' + sectionName + '" contains event "' + eventId + '"', eventData);

            if (metaData && metaData.allowed_pages && metaData.allowed_pages.length > 0
                && !metaData.allowed_pages.includes(window.location.pathname)) {
                logger('Warning: Skipping event "' + eventId + '", not in allowed pages', window.location.pathname, metaData.allowed_pages);
                continue;
            }

            if (metaData && metaData.allowed_events && metaData.allowed_events.length > 0) {
                for (const [allowedEventKey, allowedEvent] of Object.entries(metaData.allowed_events)) {
                    $(window).on(allowedEvent, function() {
                        logger('push (allowed event "' + allowedEventKey + '") [generic.js]', eventData);
                        window.dataLayer.push({ ecommerce: null });
                        window.dataLayer.push(eventData);
                    });
                }

                continue;
            }

            logger('push (event from customerData "' + sectionName + '") [generic.js]', eventData);
            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(eventData);

            if (!metaData || metaData.cacheable !== true) {
                delete sectionData['gtm_events'][eventId];
                logger('invalidating sections "' + sectionName + '"', sectionData)
                customerData.set(sectionName, sectionData);
            }

            eventData.triggered = true;
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
            moduleConfig = config;

            if (isDisabled()) {
                return;
            }

            let attributes = {};
            const sectionNames = getSectionNames();
            sectionNames.forEach(function (sectionName) {
                attributes = $.extend(getGtmDataFromSection(sectionName), attributes);
            });

            logger('initial state (js)');
            window.dataLayer = window.dataLayer || [];

            if (false === isEmpty(attributes)) {
                logger('push (attributes) [generic.js]', attributes);
                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push(attributes);
            }

            sectionNames.forEach(function (sectionName) {
                processGtmEventsFromSection(sectionName);
                subscribeToSectionDataChanges(sectionName);
            });
        }
    });
});
