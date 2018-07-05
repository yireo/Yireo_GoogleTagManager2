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
    'Magento_Customer/js/customer-data'
], function ($, _, customerData) {
    'use strict';

    var initDataLayer = function (window) {
        window.dataLayer = window.dataLayer || [];
        return window;
    };

    var monitorCart = function () {
        var cart = customerData.get('cart');
        cart.subscribe(function (updatedCart) {
            customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
        });
        return true;
    };

    var monitorCheckout = function () {
        var checkout = customerData.get('checkout-data');
        checkout.subscribe(function (updatedCart) {
            customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
        });
        return true;
    };

    var monitorCustomer = function () {
        var customer = customerData.get('customer');
        customer.subscribe(function (updatedCart) {
            customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
        });
        return true;
    };

    var monitorSections = function () {
        monitorCart();
        monitorCheckout();
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

    var getGtmOrder = function () {
        var order = customerData.get('yireo-gtm-order');
        return order();
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
                return;
            }
        }

        var orderData = getGtmOrder();
        delete orderData.data_id;
        if (!_.isEmpty(orderData)) {
            callback(orderData);
            return;
        }

        // @todo: This call is made every time again and again, while it should not
        customerData.reload(['yireo-gtm-order'], true).done(function (sections) {
            var orderData = getGtmOrder();
            delete orderData.data_id;
            if (!_.isEmpty(orderData)) {
                callback(orderData);
                return;
            }

            callback({});
        });

        callback({});
    };

    var addScriptElement = function (attributes, window, document, scriptTag, dataLayer, configId) {
        window.dataLayer.push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
        var firstScript = document.getElementsByTagName(scriptTag)[0];
        var newScript = document.createElement(scriptTag);
        var dataLayerArg = (dataLayer != 'dataLayer') ? '&l=' + dataLayer : '';
        newScript.async = true;
        newScript.src = '//www.googletagmanager.com/gtm.js?id=' + configId + dataLayerArg;
        firstScript.parentNode.insertBefore(newScript, firstScript);
    };

    return {
        'initDataLayer': initDataLayer,
        'monitorSections': monitorSections,
        'monitorCart': monitorCart,
        'monitorCustomer': monitorCustomer,
        'monitorCheckout': monitorCheckout,
        'getCustomer': getCustomer,
        'getGtmQuote': getGtmQuote,
        'getGtmOrder': getGtmOrder,
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
                attributes = $.extend(cartAttributes, attributes);

                if (config.debug) {
                    console.log(attributes);
                }

                dataLayer.push(attributes);
                addScriptElement(attributes, window, document, 'script', 'dataLayer', config.id);
            });
        }
    };
});
