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

    var isDisabled = function() {
        if (isValidConfig() === false) {
            return true;
        }

        return isAllowedByCookieRestrictionMode() === false;
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

        return $.cookie(moduleConfig.cookie_restriction_mode);
    };

    var isLoggedIn = function () {
        var customer = customerData.get('customer');
        return customer() && customer().firstname;
    };
    
    var loadCustomerAttributesOnce = function() {
        var customer = customerData.get('customer');
        var gtmOnceData = customer().gtm_once;
        if (gtmOnceData && gtmOnceData.length) {
            console.log('GTM customer once (js)', gtmOnceData);
            window.dataLayer.push(gtmOnceData);
            const customerSectionData = customer();
            customerSectionData.gtm_once = {};
            customerData.set('customer', customerSectionData);
            customerData.invalidate(['customer']);
        }
    }

    var getCustomerAttributes = function () {
        var customer = customerData.get('customer');
        var gtmData = customer().gtm;
        if (isLoggedIn() && gtmData) {
            return gtmData;
        }

        return {};
    };

    var getCartAttributes = function (callback) {
        var cart = customerData.get('cart');
        var cartData = cart();

        if (cartData.gtm_once && cartData.gtm_once.length) {
            console.log('GTM cart once (js)', cartData.gtm_once);
            window.dataLayer.push(cartData.gtm_once);
            cartData.gtm_once = {};
            customerData.set('cart', cartData);
            customerData.invalidate(['cart']);
        }

        if (cartData.gtm) {
            return cartData.gtm;
        }

        return {};
    };

    var subscribeToCartChanges = function(callback) {
        var cart = customerData.get('cart');
        cart.subscribe(function (updatedCart) {
            const attributes = getCartAttributes();
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
            const attributes = getCustomerAttributes();
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
        'getCustomerAttributes': getCustomerAttributes,
        'getCartAttributes': getCartAttributes,
        'yireoGoogleTagManager': function (config) {
            moduleConfig = config;

            if (isDisabled()) {
                return;
            }

            window.dataLayer = window.dataLayer || [];

            let attributes = {};
            attributes = $.extend(getCustomerAttributes(), attributes);
            attributes = $.extend(getCartAttributes(), attributes);

            if (isDebug()) {
                console.log('GTM initial state (js)', attributes);
            }

            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(attributes);
            
            loadCustomerAttributesOnce();

            subscribeToCartChanges();
            subscribeToCustomerChanges();
        }
    };
});
