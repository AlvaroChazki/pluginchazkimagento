/***
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @package  Chazki_ChazkiArg
 * @copyright Chazki © 2020
 * @author   Chazki
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Chazki_ChazkiArg/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Chazki_ChazkiArg/js/action/create-shipping-address-mixin': true
            }
        }
    }
    ,
    map: {
        '*': {
            // 'Magento_Checkout/template/shipping-address/address-renderer/default':
            //     'Chazki_ChazkiArg/template/shipping-address/address-renderer/default',

            // 'Magento_Checkout/template/shipping-address/address-renderer/default':
            //     'Chazki_ChazkiArg/template/checkout/shop-renderer/default',

            // 'Magento_Sales/template/order/view/info.phtml':
            //     'Chazki_ChazkiArg/template/adminhtml/default.phtml',

            'Magento_Shipping/template/order/tracking/view.phtml':
                'Chazki_ChazkiArg/template/adminhtml/view.phtml',
            'Magento_Shipping/template/order/tracking.phtml':
                'Chazki_ChazkiArg/template/adminhtml/tracking.phtml',
        }
    }
};
