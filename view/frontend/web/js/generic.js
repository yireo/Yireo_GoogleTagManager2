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

    var initDataLayer = function () {
        window.dataLayer = window.dataLayer || [];
    };

    var monitorCart = function() {
        var cart = customerData.get('cart');
        cart.subscribe(function (updatedCart) {
            customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
        });

        var checkout = customerData.get('checkout-data');
        checkout.subscribe(function (updatedCart) {
            customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
        });

        var customer = customerData.get('customer');
        customer.subscribe(function (updatedCart) {
            customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
        });
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

        return isLoggedIn() ? {
            'customerLoggedIn': 1,
            'customerId': customer.id,
            'customerGroupId': customer.group_id,
            'customerGroupCode': customer.group_code.toUpperCase()
        } : { 'customerLoggedIn': 0 };
    };

    var getCartSpecificAttributes = function () {

        var cart = customerData.get('cart');
        var quote = customerData.get('yireo-gtm-quote');
        var order = customerData.get('yireo-gtm-order');

        var quoteData = quote();
        delete quoteData.data_id;
        if (! _.isEmpty(quoteData)) {

            // This check should not be needed if the sections.xml was correctly reloading our data
            if (cart().summary_count == 0) {
                customerData.reload(['yireo-gtm-quote', 'yireo-gtm-order']);
            }

            return quoteData;
        }

        var orderData = order();
        delete orderData.data_id;
        if (! _.isEmpty(orderData)) {
            return orderData;
        }



        return {};
    };

    return function (config) {
        initDataLayer();
        monitorCart();

        var attributes = $.extend(getCartSpecificAttributes(), getCustomerSpecificAttributes(), config.attributes);

        dataLayer.push(attributes);

        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = '//www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', config.id);
    }
});