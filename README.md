# Magento 2 module for Google Tag Manager
Homepage: https://www.yireo.com/software/magento-extensions/googletagmanager2

Requirements:
* Magento 2.1.0 Stable

See `composer.json` for other requirements.

### Instructions for using composer
Use composer to install this extension. First make sure that Magento is installed via composer, and that there is a valid `composer.json` file present.

Next, install our module using the following command:

    composer require yireo/magento2-googletagmanager2

Next, install the new module into Magento itself:

    ./bin/magento module:enable Yireo_GoogleTagManager2
    ./bin/magento setup:upgrade

Check whether the module is succesfully installed in **Admin > Stores >
Configuration > Advanced > Advanced**.

Done.


## Instructions for manual copy
We recommend `composer` to install this package. If you want a manual copy instead, these are the steps. However, please note that we do NOT recommend you to do this.

* Upload the files in the `source/` folder to the folder `app/code/Yireo/GoogleTagManager2` of your site
* Run `php -f bin/magento module:enable Yireo_GoogleTagManager2`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Configure settings under `Stores > Configuration > Sales > Yireo GoogleTagManager`
* Done

## Unit testing
This extension ships with PHPUnit tests. The generic PHPUnit configuration in Magento 2 will pick up on these
tests. To only test Yireo extensions, simply run PHPUnit from within this folder. Note that this assumes that
the extension is installed via composer. For instance:

    phpunit

The JavaScript code ships with MochaJS unit tests. To install the stuff, simply run (within this extension directory) the following:

    npm install
    npm run mocha

Or just use `yarn`:

    yarn
    npm run mocha
