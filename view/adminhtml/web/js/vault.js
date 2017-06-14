define([
  'jquery',
  'uiComponent'
], function ($, Class) {
  'use strict';

  return Class.extend({
    defaults: {
      $selector: null,
      selector: 'edit_form'
    },

    initObservable: function () {
      var self = this;

      self.$selector = $('#' + self.selector);
      this._super();

      this.initEventHandlers();

      return this;
    },

    getCode: function () {
      return this.code;
    },

    initEventHandlers: function () {
      $('#' + this.container).find('[name="payment[token_switcher]"]')
        .on('click', this.setPaymentDetails.bind(this));
    },

    setPaymentDetails: function () {
      this.$selector.find('[name="payment[public_hash]"]').val(this.publicHash);
    }
  });
});