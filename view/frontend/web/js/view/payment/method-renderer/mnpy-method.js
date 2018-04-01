define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';

        var checkoutConfig = window.checkoutConfig.payment;

        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Mnpy_Magento2/payment/mnpy',
                selectedToken: null
            },
            getTokens: function() {
                return window.checkoutConfig.payment.mnpy.tokens;
            },
            afterPlaceOrder: function () {
                window.location.replace(url.build('mnpy/checkout/redirect/'));
            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "selected_token": this.selectedToken
                    }
                };
            }
        });
    }
);
