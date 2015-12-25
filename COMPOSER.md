# Instructions for using composer

Use composer to install this extension. First make sure that Magento is installed
via composer, and that there is a valid `composer.json` file present.

Next, modify the Magento 2 `composer.json` file to add the Yireo Satis repository
to it:

    {
        ...
        "repositories": {
            "magento": {
                "type": "composer",
                "url": "http://packages.magento.com"
            },
            "yireo": {
                "type":"composer",
                "url":"http://satis.yireo.com"
            }
        }
    }

Next, install our module using the following command:

    composer require yireo/yireo_googletagmanager2

Next, install the new module into Magento itself:

    ./bin/magento module:enable Yireo_GoogleTagManager2
    ./bin/magento setup:upgrade

Check whether the module is succesfully installed in **Admin > Stores >
Configuration > Advanced > Advanced**.

Done.

