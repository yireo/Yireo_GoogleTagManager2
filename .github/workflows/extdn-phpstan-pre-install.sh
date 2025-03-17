#!/bin/bash
composer config minimum-stability dev
composer config prefer-stable false

composer require yireo/magento2-integration-test-helper --no-update

composer config --no-plugins allow-plugins true

composer require --dev phpstan/extension-installer:^1.0 --no-update
composer require --dev bitexpert/phpstan-magento:^0.32 --no-update

composer require yireo/magento2-replace-bundled:^4.0 --no-update
composer require yireo/magento2-replace-inventory:^4.0 --no-update
composer require yireo/magento2-replace-pagebuilder:^4.0 --no-update
