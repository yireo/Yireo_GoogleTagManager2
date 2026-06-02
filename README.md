# Magento 2 module for Google Tag Manager

**A client-side implementation of Google Tag Manager for Magento 2**

**Also see: https://www.yireo.com/software/magento-extensions/googletagmanager2**

[![Latest Stable Version](https://poser.pugx.org/yireo/magento2-googletagmanager2/v)](//packagist.org/packages/yireo/magento2-googletagmanager2) [![Total Downloads](https://poser.pugx.org/yireo/magento2-googletagmanager2/downloads)](//packagist.org/packages/yireo/magento2-googletagmanager2)  [![License](https://poser.pugx.org/yireo/magento2-googletagmanager2/license)](//packagist.org/packages/yireo/magento2-googletagmanager2)

[![Unit Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/unit-tests.yml)
[![Static Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/static-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/static-tests.yml)
[![Integration Tests](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/integration-tests.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/integration-tests.yml)
[![DI Compilation](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/compile.yml/badge.svg)](https://github.com/yireo/Yireo_GoogleTagManager2/actions/workflows/compile.yml)

![Luma Themes compatible](https://img.shields.io/badge/Luma%20Themes-compatible-blue?style=flat)
![Hyvä Themes compatible](https://img.shields.io/badge/Hyv%C3%A4%20Themes-compatible-blue?style=flat)

## Requirements
Supported versions: Magento 2.3.7 or higher and 2.4.1 or higher (partially because of the requirement for PHP 7.4 or PHP 8.1).

See `composer.json` for other requirements.

## Additional modules
- [Yireo_GoogleTagManager2LokiCheckout](https://github.com/yireo/Yireo_GoogleTagManager2LokiCheckout) - integration with Loki Checkout;
- [Yireo_GoogleTagManager2HyvaCheckout](https://github.com/yireo/Yireo_GoogleTagManager2HyvaCheckout) - integration with Hyva Checkout;

## About version 3
The `Yireo_GoogleTagManager2` module was refactored heavily: The architecture was redesigned, GA4 support was being added, GA3 support (aka UA/EE) was being removed, click-events were added and overall there was a huge extensibility being added. The refactoring has come available under a new major version 3. If you were not using this
extension yet, you can just proceed with the composer installation which will pick this new version. If you were
using major version 2 in the past, edit your `composer.json` file manually to use the new version `^3.0`. Alternatively, update your `composer.json` by using the command `composer require yireo/magento2-googletagmanager2:^3.0 --no-update`. Next, upgrade with `composer update`.

## See also
- [Installation](INSTALL.md)
- [Usage](USAGE.md)
- [Tutorial](TUTORIAL.md)
- [FAQ](FAQ.md)
- [Architecture](ARCHITECTURE.md)
- [Testing](TESTING.md)
- [CHANGELOG](CHANGELOG.md)
- [License](LICENSE.txt)


## Support & Professional Services

This extension is released as **free and open source software**.  
You are free to use, study, modify and distribute the code according to the license terms.

While the extension itself is available at no cost, professional assistance is available for merchants, agencies and developers who require guaranteed help, customizations or consultancy.

### Professional support

Commercial support, training and consultancy can be obtained through **Yireo**:

- Magento 2 consultancy and architecture guidance
- Custom feature development
- Debugging and performance optimization
- Implementation support and best-practice advice
- Developer training and workshops

If you require professional support, please contact Yireo:

👉 https://www.yireo.com/

Yireo is a Dutch company focused on Magento development, training and extension development, actively maintaining various open-source Magento projects. 

