define(
  [
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Payment/js/model/credit-card-validation/validator',
    'Magento_Checkout/js/action/redirect-on-success',
    'stripejs'

  ],
  function ($, Component, placeOrderAction, fullScreenLoader, additionalValidators, validator, redirectOnSuccessAction) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Pmclain_Stripe/payment/form'
      },

      initialize: function() {
        this._super();
        Stripe.setPublishableKey(this.getPublishableKey());
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

        var cardInfo = {
          number: this.creditCardNumber(),
          exp_month: this.creditCardExpMonth(),
          exp_year: this.creditCardExpYear(),
          cvc: this.creditCardVerificationNumber()
        };

        var deffer = $.Deferred();

        Stripe.card.createToken(cardInfo, function(status, response) {
          if (response.error) {
            deffer.reject(response.error.message);
          }else {
            self.token = response.id;
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
        return {
          'method': this.item.method,
          'additional_data': {
            'cc_last4': this.creditCardNumber().slice(-4),
            'cc_token': this.token,
            'cc_type': this.creditCardType(),
            'cc_exp_year': this.creditCardExpYear(),
            'cc_exp_month': this.creditCardExpMonth()
          }
        };
      },

      getPublishableKey: function () {
        return window.checkoutConfig.payment[this.getCode()].publishableKey;
      },

      validate: function() {
        var $form = $('#' + this.getCode() + '-form');
        return $form.validation() && $form.validation('isValid');
      }

    });
  }
);