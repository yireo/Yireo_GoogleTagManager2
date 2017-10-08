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
            } : {'customerLoggedIn': 0};
    };

    var getCartSpecificAttributes = function () {

        var cart = customerData.get('cart');
        var quote = customerData.get('yireo-gtm-quote');
        var order = customerData.get('yireo-gtm-order');

        var quoteData = quote();
        delete quoteData.data_id;
        if (!_.isEmpty(quoteData)) {

            // This check should not be needed if the sections.xml was correctly reloading our data
            if (cart().summary_count == 0) {
                customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
            }

            return quoteData;
        }

        var orderData = order();
        delete orderData.data_id;
        if (!_.isEmpty(orderData)) {
            return orderData;
        }

        return {};
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
        'isLoggedIn': isLoggedIn,
        'getCustomerSpecificAttributes': getCustomerSpecificAttributes,
        'getCartSpecificAttributes': getCartSpecificAttributes,
        'addScriptElement': addScriptElement,
        'yireoGoogleTagManager': function (config) {
            initDataLayer(window);
            monitorSections();

            var attributes = $.extend(getCartSpecificAttributes(), getCustomerSpecificAttributes(), config.attributes);

            if (config.debug) {
                console.log(attributes);
            }

            dataLayer.push(attributes);
            addScriptElement(attributes, window, document, 'script', 'dataLayer', config.id);
        }
    };
});