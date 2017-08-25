/**
 * Jasmine tests
 */

define([
    'Yireo_GoogleTagManager2/js/generic'
], function (gtm) {
    'use strict';

    describe('GTM cart monitor', function () {
        it('more or less works', function() {
            expect(gtm.monitorCart()).toBe(true);
        });
    });
});