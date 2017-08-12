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
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, customerData) {
    'use strict';

    var initDataLayer = function () {
        window.dataLayer = window.dataLayer || [];
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

    var getQuoteSpecificAttributes = function () {
        var sectionName = 'yireo-gtm-quote';
        var quote = customerData.get(sectionName);

        return quote();
    };

    var getOrderSpecificAttributes = function () {
        var sectionName = 'yireo-gtm-order';
        var quote = customerData.get(sectionName);

        return quote();
    };

    return function (config) {
        initDataLayer();

        console.log('Order attributes:');
        console.log(getOrderSpecificAttributes());


        var attributes = $.extend(config.attributes, getCustomerSpecificAttributes(), getQuoteSpecificAttributes());

        console.log('All attributes:');
        console.log(attributes);

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