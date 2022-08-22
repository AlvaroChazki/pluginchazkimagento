/***
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @package  Chazki_ChazkiArg
 * @copyright Chazki © 2020
 * @author   Chazki
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            if (messageContainer.custom_attributes !== undefined) {
                $.each(messageContainer.custom_attributes , function(key, value) {
                    if ($.isPlainObject(value)){
                        value = value['value'];
                    }

                    messageContainer['custom_attributes'][key] = value;
                });
            }

            return originalAction(messageContainer);
        });
    };
});
