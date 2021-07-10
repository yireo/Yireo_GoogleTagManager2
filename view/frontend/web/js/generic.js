/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright (c) 2019 Yireo (https://www.yireo.com/)
 * @license     Open Software License
 */

define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
], function ($, _, customerData) {
    'use strict';

    var isDisabled = function(config) {
        if (isValidConfig(config) === false) {
            return true;
        }

        if (isAllowedByCookieRestrictionMode(config) === false) {
            return true;
        }

        return false;
    };

    var isDebug = function(config) {
        return !!config.debug;
    }

    var isValidConfig = function(config) {
        if (typeof config.id === 'undefined' || !config.id) {
            console.warn('GTM identifier empty, terminating GTM initialization.');
            return false;
        }

        return true;
    };

    var isAllowedByCookieRestrictionMode = function(config) {
        if (!config.cookie_restriction_mode) {
            return true;
        }

        if (!$.cookie(config.cookie_restriction_mode)){
            return false;
        }

        return true;
    };

    var initDataLayer = function (window) {
        window.dataLayer = window.dataLayer || [];
        return window;
    };

    var getCustomer = function () {
        var customer = customerData.get('customer');
        return customer();
    };

    var isLoggedIn = function () {
        var customer = getCustomer();
        return customer && customer.firstname;
    };

    var getCustomerSpecificAttributes = function () {
        var customer = getCustomer();
        var customerGroup = customer.group_code;
        var customerGroupCode = (customerGroup) ? customerGroup.toUpperCase() : 'UNKNOWN';

        return isLoggedIn() ? {
            'customerLoggedIn': 1,
            'customerId': customer.id,
            'customerGroupId': customer.group_id,
            'customerGroupCode': customerGroupCode
        } : {
            'customerLoggedIn': 0,
            'customerGroupId': 0,
            'customerGroupCode': 'UNKNOWN'
        };
    };

    var getCartSpecificAttributes = function (callback) {
        var cart = customerData.get('cart');

        if (cart().gtm) {
            return cart().gtm;
        }

        return {};
    };

    var existingNodes = [];

    var addScriptElement = function (attributes, window, document, scriptTag, dataLayer, configId) {
        window.dataLayer.push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
        var firstScript = document.getElementsByTagName(scriptTag)[0];
        var newScript = document.createElement(scriptTag);
        var dataLayerArg = (dataLayer != 'dataLayer') ? '&l=' + dataLayer : '';
        newScript.async = true;
        newScript.src = '//www.googletagmanager.com/gtm.js?id=' + configId + dataLayerArg;

        if (existingNodes.indexOf(newScript.src) === -1) {
            firstScript.parentNode.insertBefore(newScript, firstScript);
            existingNodes.push(newScript.src);
        }
    };

    return {
        'isValid': isValidConfig,
        'isAllowedByCookieRestrictionMode': isAllowedByCookieRestrictionMode,
        'initDataLayer': initDataLayer,
        'getCustomer': getCustomer,
        'isLoggedIn': isLoggedIn,
        'getCustomerSpecificAttributes': getCustomerSpecificAttributes,
        'getCartSpecificAttributes': getCartSpecificAttributes,
        'addScriptElement': addScriptElement,
        'yireoGoogleTagManager': function (config) {
            if (isDisabled(config)) {
                return;
            }

            initDataLayer(window);

            let attributes = config.attributes;
            attributes = $.extend(getCustomerSpecificAttributes(), attributes);
            attributes = $.extend(getCartSpecificAttributes(), attributes);

            if (isDebug(config)) {
                console.log('GTM debugging', attributes, config);
            }

            window.dataLayer.push(attributes);
            addScriptElement(attributes, window, document, 'script', 'dataLayer', config.id);
        }
    };
});
