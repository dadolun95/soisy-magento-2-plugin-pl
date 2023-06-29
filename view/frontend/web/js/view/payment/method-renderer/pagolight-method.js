define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Soisy_PaymentMethod/payment/pagolight'
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.soisy.mailingAddress;
            },

            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },

            getSimulation: function () {
                return window.checkoutConfig.payment.simulation[this.item.method] !== false ? window.checkoutConfig.payment.simulation[this.item.method]: "";
            }
        });
    }
);
