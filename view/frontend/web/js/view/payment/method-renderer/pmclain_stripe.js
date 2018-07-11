define(
  [
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Checkout/js/action/place-order',
    'Pmclain_Stripe/js/action/save-payment-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Payment/js/model/credit-card-validation/validator',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data',
    'https://js.stripe.com/v3/'
  ],
  function (
      $,
      Component,
      placeOrderAction,
      savePaymentAction,
      fullScreenLoader,
      additionalValidators,
      validator,
      redirectOnSuccessAction,
      VaultEnabler,
      quote,
      alert,
      customerData
  ) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Pmclain_Stripe/payment/form',
        stripe: null,
        stripeCardElement: null,
        stripeCard: null,
        token: null,
        source: null,
        threeDSource: null
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
          hidePostalCode: true,
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
          deferred;

        if (event) {
          event.preventDefault();
        }

        if (this.validate()) {
          this.isPlaceOrderActionAllowed(false);
          fullScreenLoader.startLoader();

          if (this.requireThreeDSecure()) {
            deferred = this.createSource();
          } else {
            deferred = this.createToken();
          }

          $.when(deferred).done(function(result) {
            if (result.hasOwnProperty('redirect')) {
              return true;
            }

            self._placeOrder();
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

      _placeOrder: function() {
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

      createToken: function() {
        var self = this,
          defer = $.Deferred();

        self.stripe.createToken(self.stripeCard, this.getAddressData()).then(function(response) {
          if (response.error) {
            defer.reject(response.error.message);
          }else {
            self.token = response.token;
            defer.resolve({});
          }
        });

        return defer.promise();
      },

      createSource: function () {
        var self = this,
          savePayment,
          defer = $.Deferred();

        self.stripe.createSource(self.stripeCard, this.getOwnerData()).then(function(response) {
          if (response.error) {
            defer.reject(response.error.message);
            return;
          }

          self.source = response.source;

          $.when(self.createThreeDSource(self.source)).done(function (response) {
            if (response.error) {
              defer.reject(response.error.message);
              return;
            }

            self.threeDSource = response.source;
            if (self.threeDSource.status !== 'pending') {
              defer.resolve({});
              return;
            }

            savePayment = savePaymentAction(self.getData(), self.messageContainer);

            $.when(savePayment).done(function () {
              defer.resolve({redirect: true});
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
              defer.reject($.mage.__('An error occurred on the server. Please try again.'));
            });
          });
        });

        return defer.promise();
      },

      createThreeDSource: function(source) {
        var self = this,
          defer = $.Deferred();

        self.stripe.createSource({
          type: 'three_d_secure',
          amount: quote.totals().base_grand_total * 100,
          currency: quote.totals().base_currency_code,
          three_d_secure: {
            card: source.id
          },
          redirect: {
            return_url: this.getThreeDRedirectUrl()
          }
        }).then(function(response) {
          defer.resolve(response);
        });

        return defer.promise();
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
          data = this.getTokenData(data);
        } else if (this.source) {
          data = this.getSourceData(data);
        }

        this.vaultEnabler.visitAdditionalData(data);

        return data;
      },

      getSourceData: function (data) {
        var card = this.source.card;

        data.additional_data.cc_exp_month = card.exp_month;
        data.additional_data.cc_exp_year = card.exp_year;
        data.additional_data.cc_last4 = card.last4;
        data.additional_data.cc_type = card.brand;
        data.additional_data.cc_src = this.source.id;

        if (this.threeDSource) {
          data.additional_data.three_d_src = this.threeDSource.id;
          data.additional_data.three_d_client_secret = this.threeDSource.client_secret;
        }

        return data;
      },

      getTokenData: function (data) {
        var card = this.token.card;

        data.additional_data.cc_exp_month = card.exp_month;
        data.additional_data.cc_exp_year = card.exp_year;
        data.additional_data.cc_last4 = card.last4;
        data.additional_data.cc_type = card.brand;
        data.additional_data.cc_token = this.token.id;

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

      requireThreeDSecure: function () {
        return window.checkoutConfig.payment[this.getCode()].threeDSecure && this.threeDThresholdMet();
      },

      threeDSecureThreshold: function () {
        return window.checkoutConfig.payment[this.getCode()].threeDThreshold;
      },

      threeDThresholdMet: function () {
        return quote.totals().base_grand_total >= this.threeDSecureThreshold();
      },

      getThreeDRedirectUrl: function() {
        return window.checkoutConfig.payment[this.getCode()].threeDRedirectUrl;
      },

      getVaultCode: function () {
        return window.checkoutConfig.payment[this.getCode()].vaultCode;
      },

      getOwnerData: function () {
        var billingAddress = quote.billingAddress(),
          ownerData = {
            owner: {
              name: billingAddress.firstname + ' ' + billingAddress.lastname,
              address: {
                line1: billingAddress.street[0],
                country: billingAddress.countryId
              }
            }
          };

          if (billingAddress.street.length === 2) {
              ownerData.owner.address.line2 = billingAddress.street[1];
          }

          if (billingAddress.hasOwnProperty('postcode')) {
              ownerData.owner.address.postal_code = billingAddress.postcode;
          }

          if (billingAddress.hasOwnProperty('regionCode')) {
              ownerData.owner.address.state = billingAddress.regionCode;
          }

          if (ownerData.owner.address.state == null){
            ownerData.owner.address.state = '';
          }

          return ownerData;
      },

      getAddressData: function () {
        var billingAddress = quote.billingAddress();

        var stripeData = {
          name: billingAddress.firstname + ' ' + billingAddress.lastname,
          address_country: billingAddress.countryId,
          address_line1: billingAddress.street[0]
        };

        if (billingAddress.street.length === 2) {
          stripeData.address_line2 = billingAddress.street[1];
        }

        if (billingAddress.hasOwnProperty('postcode')) {
          stripeData.address_zip = billingAddress.postcode;
        }

        if (billingAddress.hasOwnProperty('regionCode')) {
          stripeData.address_state = billingAddress.regionCode;
        }

        if (stripeData.address_state == null){
          stripeData.address_state = '';
        }

        return stripeData;
      }
    });
  }
);
