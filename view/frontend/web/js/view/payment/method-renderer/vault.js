/*browser:true*/
/*global define*/
define([
  'Magento_Vault/js/view/payment/method-renderer/vault'
], function (VaultComponent) {
  'use strict';

  return VaultComponent.extend({
    defaults: {
      template: 'Magento_Vault/payment/form'
    }
  });
});