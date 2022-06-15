/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'ether_payment',
                component: 'Crypto_MetamaskEthPayment/js/view/payment/method-renderer/ether-payment-renderer'
            }
        );

        return Component.extend({});
    }
);
