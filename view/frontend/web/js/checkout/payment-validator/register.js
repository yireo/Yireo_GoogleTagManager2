define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'AdPage_GTM/js/checkout/payment-validator/reload-customer-data',
        'googleTagManagerLogger'
    ],
    function (Component, additionalValidators, reloadCustomerDataValidator, logger) {
        'use strict';
        logger('Registering additional payment validator');
        additionalValidators.registerValidator(reloadCustomerDataValidator);
        return Component.extend({});
    }
);