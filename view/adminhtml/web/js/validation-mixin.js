define([
    'jquery'
], function ($) {
    'use strict'

    return function(targetWidget) {
        $.validator.addMethod('validate-ga4-container-id', function (value, element) {
            return value.split(',').every(function (id) {
                return /^GTM-[A-Z0-9]+$/.test(id.trim());
            });
        }, $.mage.__("Every container ID should look like 'GTM-' plus uppercase letters and digits"));

        return targetWidget;
    }
});
