/*browser:true*/
/*global define*/
define([
  'Magento_Vault/js/view/payment/method-renderer/vault'
], function (VaultComponent) {
  'use strict';

  return VaultComponent.extend({
    defaults: {
      template: 'Magento_Vault/payment/form'
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
    }
  });
});