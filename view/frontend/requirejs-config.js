/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright (c) 2022 Yireo (https://www.yireo.com/)
 * @license     Open Software License
 */

var config = {
    map: {
        '*': {
            'googleTagManager': 'AdPage_GTM/js/generic',
            'googleTagManagerPush': 'AdPage_GTM/js/push',
            'googleTagManagerProductClicks': 'AdPage_GTM/js/product/clicks',
            'googleTagManagerLogger': 'AdPage_GTM/js/logger'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'AdPage_GTM/js/mixins/shipping-save-processor-mixin': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'AdPage_GTM/js/mixins/catalog-add-to-cart-mixin': false
            },
            'Magento_Checkout/js/model/step-navigator': {
                'AdPage_GTM/js/mixins/step-navigator-mixin': true
            },
            'mage/dropdown': {
                'AdPage_GTM/js/mixins/minicart-mixin': true
            },
        }
    }
};
