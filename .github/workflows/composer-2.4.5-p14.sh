#!/bin/bash
cp composer.json composer-old.json
curl https://raw.githubusercontent.com/LokiCheckout/magento-2.4.5-patches/refs/heads/main/modules/patches.json -o loki-checkout-patches.json
jq -s add composer-old.json loki-checkout-patches.json > composer.json

