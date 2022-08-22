/***
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @package  Chazki_ChazkiArg
 * @copyright Chazki © 2020
 * @author   Chazki
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            let shippingAddress = quote.shippingAddress();
            console.log(shippingAddress);

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }
            console.log(shippingAddress.customAttributes);

            if (shippingAddress.customAttributes !== undefined) {
                $.each(shippingAddress.customAttributes , function(key, value) {
                    if ($.isPlainObject(value)){
                        key = value['attribute_code'];
                        value = value['value'];
                    }

                    //shippingAddress['customAttributes'][key] = value;
                    shippingAddress['extension_attributes'][key] = value;
                });
            }

            return originalAction(messageContainer);
        });
    };
});
