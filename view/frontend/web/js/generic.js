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
    'yireoGoogleTagManagerLogger'
], function ($, _, Component, customerData, logger) {
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
            console.warn('Yireo_GoogleTagManager2: identifier empty, terminating GTM initialization.');
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
        const gtmData = getGtmDataFromSection(sectionName);
        if (true === isEmpty(gtmData)) {
            return;
        }

        logger('section "' + sectionName + '" changed (customerData)', gtmData);
        window.dataLayer.push({ecommerce: null});
        window.dataLayer.push(gtmData);
    }

    var processGtmEventsFromSection = function (sectionName) {
        const sectionData = customerData.get(sectionName)();
        const gtmEvents = sectionData.gtm_events;

        if (true === isEmpty(gtmEvents)) {
            return;
        }

        for (const [eventId, eventData] of Object.entries(gtmEvents)) {
            logger('customerData section "' + sectionName + '" contains event "' + eventId + '"', eventData);

            window.dataLayer.push(eventData);

            if (eventData.cacheable !== true) {
                delete sectionData['gtm_events'][eventId];
                logger('invalidating sections "' + sectionName + '"', sectionData)
                customerData.set(sectionName, sectionData);
            }
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
        return Object.keys(JSON.parse(localStorage.getItem('mage-cache-storage')));
    }

    var isEmpty = function (variable) {
        if (typeof variable === 'undefined') {
            return true;
        }

        if (Array.isArray(variable) && variable.length === 0) {
            return true;
        }

        if (typeof variable === 'object' && Object.keys(variable).length === 0) {
            return true;
        }

        return false;
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

            logger('initial state (js)', attributes);
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({ecommerce: null});

            if (false === isEmpty(attributes)) {
                window.dataLayer.push(attributes);
            }

            sectionNames.forEach(function (sectionName) {
                processGtmEventsFromSection(sectionName);
                subscribeToSectionDataChanges(sectionName);
            });
        }
    });
});
