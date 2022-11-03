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
    'Magento_Customer/js/customer-data',
], function ($, _, customerData) {
    'use strict';

    var moduleConfig = {};

    var isDisabled = function () {
        if (isValidConfig() === false) {
            return true;
        }

        return isAllowedByCookieRestrictionMode() === false;
    };

    var isDebug = function () {
        return !!moduleConfig.debug;
    }

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

    var loadGtmOnceDataFromSection = function (sectionName) {
        const sectionData = customerData.get(sectionName)();
        const gtmData = sectionData.gtm_once;
        if (typeof gtmData === 'undefined') {
            return;
        }

        if (Object.entries(gtmData).length < 1) {
            return;
        }

        for (const [eventId, eventData] of Object.entries(gtmData)) {
            if (isDebug()) {
                console.log('Yireo_GoogleTagManager2: ' + sectionName + ' event "' + eventId + '" (js)', eventData);
            }

            window.dataLayer.push(eventData);
        }

        delete sectionData.gtm_once;
        customerData.set(sectionName, sectionData);
        customerData.invalidate([sectionName]);
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
        sectionData.subscribe(function (updatedCustomer) {
            const gtmData = getGtmDataFromSection(sectionName);
            if (isDebug()) {
                console.log('Yireo_GoogleTagManager2: ' + sectionName + ' change (js)', gtmData);
            }

            window.dataLayer.push({ecommerce: null});
            window.dataLayer.push(gtmData);
        });
    }

    return {
        'yireoGoogleTagManager': function (config) {
            moduleConfig = config;

            if (isDisabled()) {
                return;
            }

            let attributes = {};
            // @todo: Make this dynamically work for ALL sections
            const sectionNames = ['customer', 'cart'];
            sectionNames.forEach(function (sectionName) {
                attributes = $.extend(getGtmDataFromSection(sectionName), attributes);
            });

            if (isDebug()) {
                console.log('Yireo_GoogleTagManager2: initial state (js)', attributes);
            }

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({ecommerce: null});
            window.dataLayer.push(attributes);

            sectionNames.forEach(function (sectionName) {
                loadGtmOnceDataFromSection(sectionName);
                subscribeToSectionDataChanges(sectionName);
            });
        }
    };
});
