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
            'yireoGoogleTagManager': 'Yireo_GoogleTagManager2/js/generic'
        }
    },
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Yireo_GoogleTagManager2/js/mixins/catalog-add-to-cart-mixin': true
            }
        }
    }
};
