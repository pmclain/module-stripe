/*browser:true*/
/*global define*/
define([
  'jquery',
  'Magento_Vault/js/view/payment/method-renderer/vault',
  'Magento_Checkout/js/action/place-order',
  'Pmclain_Stripe/js/action/save-payment-information',
  'Magento_Checkout/js/model/full-screen-loader',
  'Magento_Checkout/js/action/redirect-on-success',
  'Magento_Checkout/js/model/quote',
  'Magento_Ui/js/modal/alert',
  'Magento_Customer/js/customer-data',
  'https://js.stripe.com/v3/'
], function (
    $,
    VaultComponent,
    placeOrderAction,
    savePaymentAction,
    fullScreenLoader,
    redirectOnSuccessAction,
    quote,
    alert,
    customerData
) {
  'use strict';

  return VaultComponent.extend({
    defaults: {
      template: 'Magento_Vault/payment/form',
      stripe: null,
      threeDSource: null
    },

    initialize: function() {
      this._super();
      this.stripe = Stripe(this.getPublishableKey());
    },

    placeOrder: function (data, event) {
      var self = this,
        savePayment;

      if (event) {
        event.preventDefault();
      }

      this.isPlaceOrderActionAllowed(false);
      fullScreenLoader.startLoader();

      if (!this.requireThreeDSecure()) {
          this._super(data, event);
          return true;
      }

      $.when(self.createThreeDSource()).done(function(response) {
        if (self.threeDSource.status !== 'pending') {
          self._placeOrder();
          return true;
        }

        savePayment = savePaymentAction(self.getData(), self.messageContainer);

        $.when(savePayment).done(function () {
          fullScreenLoader.stopLoader();
          alert({
            title: $.mage.__('Additional Payment Verification Required'),
            content: $.mage.__('Your card issue has requested additional verification before completing your order. You will be redirected to the issuer\'s website after closing this notification.'),
            actions: {
              always: function() {
                customerData.invalidate(['cart']);
                window.location = response.source.redirect.url;
              }
            }
          });
        }).fail(function () {
          fullScreenLoader.stopLoader();
          self.isPlaceOrderActionAllowed(true);
          self.messageContainer.addErrorMessage({
              'message': $.mage.__('An error occurred on the server. Please try again.')
          });
        });
      }).fail(function(result) {
        fullScreenLoader.stopLoader();
        self.isPlaceOrderActionAllowed(true);

        self.messageContainer.addErrorMessage({
          'message': result
        });
      });
    },

    _placeOrder: function () {
      var self = this,
        placeOrder = placeOrderAction(self.getData(), self.messageContainer);

      $.when(placeOrder).done(function() {
        if (self.redirectAfterPlaceOrder) {
          redirectOnSuccessAction.execute();
        }
      }).fail(function() {
        fullScreenLoader.stopLoader();
        self.isPlaceOrderActionAllowed(true);
      });
    },

    createThreeDSource: function () {
      var self = this,
        defer = $.Deferred();

      self.stripe.createSource({
        type: 'three_d_secure',
        amount: quote.totals().base_grand_total * 100,
        currency: quote.totals().base_currency_code,
        three_d_secure: {
          card: self.details.source
        },
        redirect: {
          return_url: this.getThreeDRedirectUrl()
        }
      }).then(function (response) {
        if (response.error) {
          defer.reject(response.error.message);
          return;
        }

        self.threeDSource = response.source;
        defer.resolve(response);
      });

      return defer.promise();
    },

    getMaskedCard: function () {
      return this.details.maskedCC;
    },

    getExpirationDate: function () {
      return this.details.expirationDate;
    },

    getCardType: function () {
      return this.details.type;
    },

    getToken: function() {
      return this.publicHash;
    },

    getPublishableKey: function () {
      return window.checkoutConfig.payment.pmclain_stripe.publishableKey;
    },

    getData: function () {
      var data = this._super();

      if (this.threeDSource) {
        data.additional_data.three_d_src = this.threeDSource.id;
        data.additional_data.three_d_client_secret = this.threeDSource.client_secret;
      }

      return data;
    },

    requireThreeDSecure: function () {
      return window.checkoutConfig.payment.pmclain_stripe.threeDSecure
        && this.threeDThresholdMet()
        && this.details.threeDSecure;
    },

    threeDSecureThreshold: function () {
      return window.checkoutConfig.payment.pmclain_stripe.threeDThreshold;
    },

    threeDThresholdMet: function () {
      return quote.totals().base_grand_total >= this.threeDSecureThreshold();
    },

    getThreeDRedirectUrl: function() {
      return window.checkoutConfig.payment.pmclain_stripe.threeDRedirectUrl;
    }
  });
});