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

    var moduleConfig = {};

    var isDisabled = function() {
        if (isValidConfig() === false) {
            return true;
        }

        if (isAllowedByCookieRestrictionMode() === false) {
            return true;
        }

        return false;
    };

    var isDebug = function() {
        return !!moduleConfig.debug;
    }

    var isValidConfig = function() {
        if (typeof moduleConfig.id === 'undefined' || !moduleConfig.id) {
            console.warn('GTM identifier empty, terminating GTM initialization.');
            return false;
        }

        return true;
    };

    var isAllowedByCookieRestrictionMode = function() {
        if (!moduleConfig.cookie_restriction_mode) {
            return true;
        }

        if (!$.cookie(moduleConfig.cookie_restriction_mode)){
            return false;
        }

        return true;
    };

    var isLoggedIn = function () {
        var customer = customerData.get('customer');
        return customer() && customer().firstname;
    };

    var getCustomerSpecificAttributes = function () {
        var customer = customerData.get('customer');
        var customerGroup = customer().gtm.group_code;
        var customerGroupCode = (customerGroup) ? customerGroup.toUpperCase() : 'UNKNOWN';

        return isLoggedIn() ? {
            'customerLoggedIn': 1,
            'customerId': customer().gtm.id,
            'customerGroupId': customer().gtm.group_id,
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

    var subscribeToCartChanges = function(callback) {
        var cart = customerData.get('cart');
        cart.subscribe(function (updatedCart) {
            const attributes = getCartSpecificAttributes();
            if (isDebug()) {
                console.log('GTM cart change (js)', attributes);
            }

            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(attributes);
        });
    }

    var subscribeToCustomerChanges = function(callback) {
        var customer = customerData.get('customer');
        customer.subscribe(function (updatedCustomer) {
            const attributes = getCartSpecificAttributes();
            if (isDebug()) {
                console.log('GTM customer change (js)', attributes);
            }

            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(attributes);
        });
    }

    return {
        'isValid': isValidConfig,
        'isAllowedByCookieRestrictionMode': isAllowedByCookieRestrictionMode,
        'isLoggedIn': isLoggedIn,
        'getCustomerSpecificAttributes': getCustomerSpecificAttributes,
        'getCartSpecificAttributes': getCartSpecificAttributes,
        'yireoGoogleTagManager': function (config) {
            moduleConfig = config;

            if (isDisabled()) {
                return;
            }

            window.dataLayer = window.dataLayer || [];

            let attributes = {};
            attributes = $.extend(getCustomerSpecificAttributes(), attributes);
            attributes = $.extend(getCartSpecificAttributes(), attributes);

            if (isDebug()) {
                console.log('GTM initial state (js)', attributes);
            }

            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(attributes);

            subscribeToCartChanges();
            subscribeToCustomerChanges();
        }
    };
});
