define([
    'jquery',
], function ($) {
    'use strict';

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
