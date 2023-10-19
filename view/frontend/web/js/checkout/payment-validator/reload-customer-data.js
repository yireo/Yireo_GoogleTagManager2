define([
    'Magento_Customer/js/customer-data',
    'googleTagManagerLogger'
], function (customerData, logger) {
    'use strict';
    return {
        validate: function () {
            logger('payment-validator-reload-customer-data');
            customerData.reload(['cart'], true);
            return true;
        }
    }
});