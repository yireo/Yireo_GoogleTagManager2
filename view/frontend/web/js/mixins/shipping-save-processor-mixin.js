define([
    'mage/utils/wrapper',
    'Magento_Customer/js/customer-data',
    'yireoGoogleTagManagerLogger'
], function (wrapper, customerData, logger) {
    'use strict';

    return function (shippingSaveProcessor) {
        shippingSaveProcessor.saveShippingInformation = wrapper.wrapSuper(shippingSaveProcessor.saveShippingInformation, function (type) {
            const rt = this._super(type);
            logger('shipping-save-processor-mixin', type);
            customerData.reload(['cart']);
            return rt;
        })

        return shippingSaveProcessor;
    };
});
