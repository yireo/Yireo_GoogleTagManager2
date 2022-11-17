define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Yireo_GoogleTagManager2/js/checkout/payment-validator/reload-customer-data'
    ],
    function (Component, additionalValidators, reloadCustomerDataValidator) {
        'use strict';
        additionalValidators.registerValidator(reloadCustomerDataValidator);
        return Component.extend({});
    }
);