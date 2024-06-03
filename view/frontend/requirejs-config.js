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
            'googleTagManager': 'Tagging_GTM/js/generic',
            'googleTagManagerPush': 'Tagging_GTM/js/push',
            'googleTagManagerProductClicks': 'Tagging_GTM/js/product/clicks',
            'googleTagManagerLogger': 'Tagging_GTM/js/logger'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Tagging_GTM/js/mixins/shipping-save-processor-mixin': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Tagging_GTM/js/mixins/catalog-add-to-cart-mixin': false
            },
            'Magento_Checkout/js/model/step-navigator': {
                'Tagging_GTM/js/mixins/step-navigator-mixin': true
            }
        }
    }
};
