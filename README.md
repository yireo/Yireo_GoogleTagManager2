# Magento 2 module for Google Tag Manager
Homepage: https://www.yireo.com/software/magento-extensions/googletagmanager2

**Currently, the `Yireo_GoogleTagManager2` module is being refactored heavily: The architecture is redesigned, GA4 support is being added, GA3 support (aka UA/EE) is being removed, click-events are added and overall there is a huge extensibility being added. The refactoring will come available under a new major version 3 and the development takes place in the `3.0-dev` branch of this extension. To get started with this easily, first add this GitHub repository as a new composer repository using the command `composer config repositories.yireo-magento2-googletagmanager2 git https://github.com/yireo/Yireo_GoogleTagManager2/`. Next, edit your project its `composer.json` file and make sure the entry for this repository is located before the main Magento sources (Mage-OS mirror or Magento Marketplace). Next, require the development package via `composer require yireo/magento2-googletagmanager2:dev-3.0-dev`.**

[![Latest Stable Version](https://poser.pugx.org/yireo/magento2-googletagmanager2/v)](//packagist.org/packages/yireo/magento2-googletagmanager2) [![Total Downloads](https://poser.pugx.org/yireo/magento2-googletagmanager2/downloads)](//packagist.org/packages/yireo/magento2-googletagmanager2)  [![License](https://poser.pugx.org/yireo/magento2-googletagmanager2/license)](//packagist.org/packages/yireo/magento2-googletagmanager2)


Supported versions:
* Magento 2.2
* Magento 2.3
* Magento 2.4

See `composer.json` for other requirements.

[![ExtDN Unit Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-unit-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-unit-tests.yml)
[![ExtDN Static Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-static-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-static-tests.yml)
[![ExtDN Integration Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-integration-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-integration-tests.yml)


## See also
- [Installation](INSTALL.md)
- [Usage](USAGE.md)
- [Testing](TESTING.md)
- [CHANGELOG](CHANGELOG.md)
- [License](LICENSE.txt)
