# Testing
## Unit testing
This extension ships with PHPUnit tests. The generic PHPUnit configuration in Magento 2 will pick up on these
tests. To only test Yireo extensions, simply run PHPUnit from within this folder. Note that this assumes that
the extension is installed via composer. For instance:

    phpunit

Also note that Mockery (`mockery/mockery`) is used for the integration tests. If you want to test this module, you need to install its dev-dependencies:

    composer require yireo/magento2-googletagmanager2 --dev

The JavaScript code ships with MochaJS unit tests. To install the stuff, simply run (within this extension directory) the following:

    npm install
    npm run mocha

Or just use `yarn`:

    yarn
    npm run mocha


