/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Cmsbox_Mercanet/js/view/payment/adapter',
        'Magento_Checkout/js/action/place-order',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/translate'
    ],
    function($, Component, Adapter, PlaceOrderAction, Url, FullScreenLoader, AdditionalValidators, t) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;
        var code = 'redirect_method';

        return Component.extend({
            defaults: {
                template: Adapter.getName() + '/payment/' + code + '.phtml',
                moduleId: Adapter.getCode(),
                methodId: Adapter.getMethodId(code),
                config: Adapter.getPaymentConfig()[Adapter.getMethodId(code)],
                targetButton:  Adapter.getMethodId(code) + '_button',
                targetForm:  Adapter.getMethodId(code) + '_form',
                redirectAfterPlaceOrder: true
            },

            /**
             * @returns {exports}
             */
            initialize: function() {
                this._super();
                Adapter.setEmailAddress();
                this.data = {'method': this.methodId};
            },

            initObservable: function() {
                this._super().observe([]);
                return this;
            },

            /**
             * @returns {string}
             */
            getCode: function() {
                return this.methodId;
            },

            /**
             * @returns {bool}
             */
            isActive: function() {
                return this.config.active;
            },

            /**
             * @returns {bool}
             */
            cartIsEmpty: function() {
                // Set the default response
                var output = false;

                // Prepare the cart check URL
                var cartCheckUrl = Url.build(this.moduleId + '/cart/state');

                // Perform the cart check request
                $.ajax({
                    type: "POST",
                    url: cartCheckUrl,
                    async: false,
                    success: function(res) {
                        output = res;
                    },
                    error: function(request, status, error) {
                        alert(error);
                    }
                });

                return JSON.parse(output.cartIsEmpty);
            },

            /**
             * @returns {string}
             */
            proceedWithSubmission: function() {
                // Disable jQuery validate checks
                $('#' + this.targetForm).validate().cancelSubmit = true;

                // Submit the form
                $('#' + this.targetForm).submit();
            },

            getPlaceOrderDeferredObject: function() {
                return $.when(
                    PlaceOrderAction(this.data, this.messageContainer)
                );
            },

            /**
             * @returns {string}
             */
            beforePlaceOrder: function() {
                // Start the loader
                FullScreenLoader.startLoader();

                // Validate before submission
                if (AdditionalValidators.validate()) {
                    // Check cart and submit
                    if (!this.cartIsEmpty()) {
                        this.proceedWithSubmission();
                    }
                    else {
                        FullScreenLoader.stopLoader();
                        alert(t('The session has expired. Please reload the page before proceeding.'));
                    }
                }
                else {
                    FullScreenLoader.stopLoader();
                }
            }
        });
    }
);