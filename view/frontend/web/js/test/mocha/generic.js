/**
 * MochaJS tests
 */

const requirejs = require('requirejs');
requirejs.config({
    baseUrl: __dirname,
    nodeRequire: require,
    paths: {
        'generic': '../../generic',
        'jquery': './mock/jquery',
        'underscore': './mock/underscore',
        'Magento_Customer/js/customer-data': './mock/customerData'
    }
});

var jsdom = require('jsdom');
const { JSDOM } = jsdom;

const dom = new JSDOM(`<!DOCTYPE html><p>Hello world</p>`);
var window = dom.window;
var document = window.document;
var config = {'attributes': {'id': 'my-gtm-test-id'}};
var generic = requirejs('generic');

var assert = require('assert');

describe('GTM dataLayer', function () {
    it('is not already initialized in window', function () {
        assert.equal(window.dataLayer, undefined);
    });

    it('is properly initialized through our script', function () {
        var generic = requirejs('generic');
        assert.ok(generic.initDataLayer(window));
    });

    it('is part of window', function () {
        var generic = requirejs('generic');
        window = generic.initDataLayer(window);
        assert.notStrictEqual(window.dataLayer, []);
    });
});

describe('GTM customer section', function () {
    it('looks ok', function() {
        var generic = requirejs('generic');
        assert.ok(generic.getCustomer());
    });
});