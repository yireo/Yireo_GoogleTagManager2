var requirejs = require('requirejs');
requirejs.config({
    baseUrl: __dirname,
    nodeRequire: require,
    paths: {
        'generic': '../generic',
        'jquery': './mock/jquery',
        'underscore': './mock/underscore',
        'Magento_Customer/js/customer-data': './mock/customerData'
    }
});

global.window = {
    document: {
        querySelector: function () {
            return null;
        },
        getDocumentElementById: function() {
            return false;
        },
        getElementsByTagName: function() {
            return [];
        },
        createElement: function() {
            return {
                'parentNode': {}
            };
        }
    }
};

global.document = window.document;

global.dataLayer = {
    'push': function () {
    }
};

var config = {'attributes': {'foo': 'bar'}};
var generic = requirejs('generic');
console.log(generic(config));

var assert = require('assert');

/*
 describe('Generic test-suite', function() {
 it('Things should load', function() {
 assert.equal(true, true);
 });
 });
 */
