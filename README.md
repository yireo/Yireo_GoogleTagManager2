# Magento 2 module for Google Tag Manager

**A client-side implementation of Google Tag Manager for Magento 2**

**Also see: https://www.yireo.com/software/magento-extensions/googletagmanager2**

**Currently, the `Yireo_GoogleTagManager2` module has been refactored heavily: The architecture is redesigned, GA4
support is being added, GA3 support (aka UA/EE) is being removed, click-events are added and overall there is a huge
extensibility being added. The refactoring has come available under a new major version 3. If you were not using this
extension yet, you can just proceed with the composer installation which will pick this new version. If you were
using major version 2 in the past, edit your `composer.json` file manually to use the new version `^3.0`. Alternatively, update your `composer.json` by using the command `composer require yireo/magento2-googletagmanager2:^3.0 --no-update`. Next, upgrade with `composer update`.**

[![Latest Stable Version](https://poser.pugx.org/yireo/magento2-googletagmanager2/v)](//packagist.org/packages/yireo/magento2-googletagmanager2) [![Total Downloads](https://poser.pugx.org/yireo/magento2-googletagmanager2/downloads)](//packagist.org/packages/yireo/magento2-googletagmanager2)  [![License](https://poser.pugx.org/yireo/magento2-googletagmanager2/license)](//packagist.org/packages/yireo/magento2-googletagmanager2)

[![ExtDN Unit Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-unit-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-unit-tests.yml)
[![ExtDN Static Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-phpstan.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-phpstan.yml)
[![ExtDN Integration Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-integration-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/extdn-integration-tests.yml)

## Requirements
Supported versions: Magento 2.3.7 or higher and 2.4.1 or higher (partially because of the requirement for PHP 7.4 or PHP 8.1).

See `composer.json` for other requirements.

## See also
- [Installation](INSTALL.md)
- [Usage](USAGE.md)
- [Tutorial](TUTORIAL.md)
- [Tutorial with Consent](TUTORIAL_CONSENT.md)
- [FAQ](FAQ.md)
- [Architecture](ARCHITECTURE.md)
- [Testing](TESTING.md)
- [CHANGELOG](CHANGELOG.md)
- [License](LICENSE.txt)
