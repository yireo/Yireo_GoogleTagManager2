phantom.casperTest = true;

var utils = require('utils');
var casper = require('casper').create();

casper.start('https://magento2.yireo.dev/what-is-new.html');

casper.then(function() {
    this.echo('First Page2: ' + this.getTitle());
});

/*
casper.start('https://magento2.yireo.dev/').wait(5000, function(){

    var currentWindow = this.evaluate(function _evaluate() {
        return window;
    });
    //this.echo(utils.dump(currentWindow.dataLayer));
});

casper.then(function() {
    this.echo('First Page: ' + this.getTitle());
});
*/

/*
casper.test.begin('Hello, Test!', 1, function(test) {
    test.assert(true);
    test.done();
});
*/

casper.run();