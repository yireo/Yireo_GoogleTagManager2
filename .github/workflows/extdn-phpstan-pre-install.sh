#!/bin/bash
composer config minimum-stability dev
composer config prefer-stable false

composer require yireo/magento2-integration-test-helper --no-update

composer config allow-plugins.phpstan/extension-installer true
composer magento/composer-dependency-version-audit-plugin true

composer require --dev phpstan/extension-installer --no-update
composer require --dev bitexpert/phpstan-magento --no-update

composer require yireo/magento2-replace-bundled:^4.0 --no-update
composer require yireo/magento2-replace-inventory:^4.0 --no-update
composer require yireo/magento2-replace-pagebuilder:^4.0 --no-update
