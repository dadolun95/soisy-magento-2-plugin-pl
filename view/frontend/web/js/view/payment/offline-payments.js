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
                type: 'soisy',
                component: 'Soisy_PaymentMethod/js/view/payment/method-renderer/pagolight-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
