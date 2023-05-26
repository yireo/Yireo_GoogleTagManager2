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
            'yireoGoogleTagManager': 'Yireo_GoogleTagManager2/js/generic',
            'yireoGoogleTagManagerProductClicks': 'Yireo_GoogleTagManager2/js/product/clicks',
            'yireoGoogleTagManagerLogger': 'Yireo_GoogleTagManager2/js/logger'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Yireo_GoogleTagManager2/js/mixins/shipping-save-processor-mixin': true
            },
            /*'Magento_Catalog/js/catalog-add-to-cart': {
                'Yireo_GoogleTagManager2/js/mixins/catalog-add-to-cart-mixin': false
            },*/
            'Magento_Checkout/js/model/step-navigator': {
                'Yireo_GoogleTagManager2/js/mixins/step-navigator-mixin': true
            },
            'mage/dropdown': {
                'Yireo_GoogleTagManager2/js/mixins/minicart-mixin': true
            },
        }
    }
};
