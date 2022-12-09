define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Yireo_GoogleTagManager2/js/checkout/payment-validator/reload-customer-data',
        'yireoGoogleTagManagerLogger'
    ],
    function (Component, additionalValidators, reloadCustomerDataValidator, logger) {
        'use strict';
        logg('Registering additional payment validator');
        additionalValidators.registerValidator(reloadCustomerDataValidator);
        return Component.extend({});
    }
);