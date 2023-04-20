define([
    'jquery'
], function ($) {
    'use strict'

    return function(targetWidget) {
        $.validator.addMethod('validate-ga4-container-id', function (value, element) {
            return value.startsWith('GTM-');
        }, $.mage.__("The container ID should start with 'GTM-'"));

        return targetWidget;
    }
});
