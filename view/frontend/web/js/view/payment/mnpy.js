define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'mnpy',
                component: 'Mnpy_Magento2/js/view/payment/method-renderer/mnpy-method'
            }
        );

        return Component.extend({});
    }
);