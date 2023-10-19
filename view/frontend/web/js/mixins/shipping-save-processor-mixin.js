define([
    'mage/utils/wrapper',
    'Magento_Customer/js/customer-data',
    'googleTagManagerLogger'
], function (wrapper, customerData, logger) {
    'use strict';

    return function (shippingSaveProcessor) {
        shippingSaveProcessor.saveShippingInformation = wrapper.wrapSuper(shippingSaveProcessor.saveShippingInformation, function (type) {
            const rt = this._super(type);
            rt.done(function() {
                logger('customerData reload (shipping-save-processor-mixin.js)', type);
                customerData.reload(['gtm-checkout'], true);
            });

            return rt;
        })

        return shippingSaveProcessor;
    };
});
