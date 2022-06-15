/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($,
              quote,
              urlBuilder,
              storage,
              customerData,
              Component,
              placeOrderAction,
              selectPaymentMethodAction,
              customer,
              checkoutData,
              additionalValidators,
              url,
              fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Crypto_MetamaskEthPayment/payment/ether_payment',
                redirectAfterPlaceOrder: false,
                ethCode: 'ETH'
            },
            afterPlaceOrder: function (id) {
                self.redirectAfterPlaceOrder = false
                window.location.replace(
                    url.build(
                        'ethpayment/payment/processing?nocache=' + (new Date().getTime())
                    ));
            }
        });
    }
);
