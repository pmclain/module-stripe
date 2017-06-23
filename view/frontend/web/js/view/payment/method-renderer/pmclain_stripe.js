define(
  [
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Payment/js/model/credit-card-validation/validator',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Vault/js/view/payment/vault-enabler',
    'stripejs'
  ],
  function ($, Component, placeOrderAction, fullScreenLoader, additionalValidators, validator, redirectOnSuccessAction, VaultEnabler) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Pmclain_Stripe/payment/form',
        stripe: null,
        stripeCardElement: null,
        stripeCard: null,
        token: null
      },

      initialize: function() {
        this._super();
        this.stripe = Stripe(this.getPublishableKey());
        this.vaultEnabler = new VaultEnabler();
        this.vaultEnabler.setPaymentCode(this.getVaultCode());
      },

      initStripeElement: function() {
        var self = this;
        self.stripeCardElement = self.stripe.elements();
        self.stripeCard = self.stripeCardElement.create('card', {
          style: {
            base: {
              fontSize: '20px'
            }
          }
        });
        self.stripeCard.mount('#stripe-card-element');
      },

      placeOrder: function(data, event) {
        var self = this,
          placeOrder;

        if (event) {
          event.preventDefault();
        }

        if (this.validate()) {
          this.isPlaceOrderActionAllowed(false);
          fullScreenLoader.startLoader();

          $.when(this.createToken()).done(function() {
            placeOrder = placeOrderAction(self.getData(), self.messageContainer);
            $.when(placeOrder).done(function() {
              if (self.redirectAfterPlaceOrder) {
                redirectOnSuccessAction.execute();
              }
            }).fail(function() {
              fullScreenLoader.stopLoader();
              self.isPlaceOrderActionAllowed(true);
            });
          }).fail(function(result) {
            fullScreenLoader.stopLoader();
            self.isPlaceOrderActionAllowed(true);

            self.messageContainer.addErrorMessage({
              'message': result
            });
          });

          return true;
        }
        return false;
      },

      createToken: function() {
        var self = this;

        var deffer = $.Deferred();

        self.stripe.createToken(self.stripeCard).then(function(response) {
          if (response.error) {
            deffer.reject(response.error.message);
          }else {
            self.token = response.token;
            deffer.resolve();
          }
        });

        return deffer.promise();
      },

      getCode: function() {
        return 'pmclain_stripe';
      },

      isActive: function() {
        return true;
      },

      getData: function() {
        var data = this._super();

        if (this.token) {
          var card = this.token.card;

          data.additional_data.cc_exp_month = card.exp_month;
          data.additional_data.cc_exp_year = card.exp_year;
          data.additional_data.cc_last4 = card.last4;
          data.additional_data.cc_type = card.brand;
          data.additional_data.cc_token = this.token.id;
        }

        this.vaultEnabler.visitAdditionalData(data);

        return data;
      },

      getPublishableKey: function () {
        return window.checkoutConfig.payment[this.getCode()].publishableKey;
      },

      validate: function() {
        var $form = $('#' + this.getCode() + '-form');
        return $form.validation() && $form.validation('isValid');
      },

      isVaultEnabled: function () {
        return this.vaultEnabler.isVaultEnabled();
      },

      getVaultCode: function () {
        return window.checkoutConfig.payment[this.getCode()].vaultCode;
      }

    });
  }
);