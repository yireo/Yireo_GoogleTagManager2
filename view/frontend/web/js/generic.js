/**
 * GoogleTagManager2 plugin for Magento
 *
 * @package     Yireo_GoogleTagManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright (c) 2017 Yireo (https://www.yireo.com/)
 * @license     Open Software License
 */

define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Yireo_GoogleTagManager2/js/quote-wrapper'
], function ($, _, customerData, quoteWrapper) {
    'use strict';

    var initDataLayer = function (window) {
        window.dataLayer = window.dataLayer || [];
        return window;
    };

    var monitorCart = function () {
        var cart = customerData.get('cart');
        cart.subscribe(reloadSections);
        return true;
    };

    var monitorQuote = function () {
        if (quoteWrapper.isQuoteAvailable()) {
            var quote = quoteWrapper.getQuote();
            if (quote.totals) {
                quote.totals.subscribe(reloadSections);
            }
        }
        return true;
    };

    var monitorCustomer = function () {
        var customer = customerData.get('customer');
        customer.subscribe(reloadSections);
        return true;
    };

    var reloadSections = _.debounce(function (data) {
        customerData.reload(['yireo-gtm-quote']);
    }, 1000, true);

    var monitorSections = function () {
        monitorCart();
        monitorQuote();
        monitorCustomer();
    };

    var getCustomer = function () {
        var customer = customerData.get('customer');
        return customer();
    };

    var getGtmQuote = function () {
        var quote = customerData.get('yireo-gtm-quote');
        return quote();
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

        if (cart().summary_count > 0) {
            var quoteData = getGtmQuote();
            delete quoteData.data_id;
            if (!_.isEmpty(quoteData)) {
                callback(quoteData);
            }
        }

        callback({});
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
        'initDataLayer': initDataLayer,
        'monitorSections': monitorSections,
        'monitorCart': monitorCart,
        'monitorCustomer': monitorCustomer,
        'monitorQuote': monitorQuote,
        'getCustomer': getCustomer,
        'getGtmQuote': getGtmQuote,
        'isLoggedIn': isLoggedIn,
        'getCustomerSpecificAttributes': getCustomerSpecificAttributes,
        'getCartSpecificAttributes': getCartSpecificAttributes,
        'addScriptElement': addScriptElement,
        'yireoGoogleTagManager': function (config) {
            if (typeof config.id === 'undefined' || !config.id) {
                console.warn('GTM identifier empty, terminating GTM initialization.');
                return;
            }

            initDataLayer(window);
            monitorSections();

            var attributes = $.extend(getCustomerSpecificAttributes(), config.attributes);

            getCartSpecificAttributes(function (cartAttributes) {
                if (cartAttributes) {
                    attributes = $.extend(cartAttributes, attributes);
                }

                if (config.debug) {
                    console.log(attributes);
                }

                dataLayer.push(attributes);
                addScriptElement(attributes, window, document, 'script', 'dataLayer', config.id);
            });
        }
    };
});
