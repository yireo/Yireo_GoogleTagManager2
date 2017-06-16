define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, customerData) {
    'use strict';

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

        return {
            'customerLoggedIn': isLoggedIn() ? 1 : 0,
            'customerId': customer.id,
            'customerGroupId': customer.group_id,
            'customerGroupCode': customer.group_code.toUpperCase()
        };
    };

    return function (config) {
        window.dataLayer = window.dataLayer || [];

        var attributes = $.extend(config.attributes, getCustomerSpecificAttributes());

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