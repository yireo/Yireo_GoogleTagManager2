define([
    'jquery',
], function ($) {
    'use strict';

    const enabled = window.AdPage_GTM_ENABLED;
    if (enabled === null || enabled === undefined) {
        return function (targetWidget) {
            return $.mage.dropdownDialog;
        };
    }

    var mixin = {
        open: function () {
            const rt = this._super();
            window.dispatchEvent(new CustomEvent('minicart_collapse'));
            return rt;
        }
    };

    return function (targetWidget) {
        $.widget('mage.dropdownDialog', targetWidget, mixin);
        return $.mage.dropdownDialog;
    };
});
