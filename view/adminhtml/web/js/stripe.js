/*browser:true*/
/*global define*/
define([
  'jquery',
  'uiComponent',
  'Magento_Ui/js/modal/alert',
  'Magento_Ui/js/lib/view/utils/dom-observer',
  'mage/translate'
], function ($, Class, alert, domObserver, $t) {
  'use strict';

  return Class.extend({

    defaults: {
      $selector: null,
      selector: 'edit_form',
      container: 'payment_form_pmclain_stripe',
      active: false,
      scriptLoaded: false,
      stripe: null,
      selectedCardType: null,
      imports: {
        onActiveChange: 'active'
      }
    },

    /**
     * Set list of observable attributes
     * @returns {exports.initObservable}
     */
    initObservable: function () {
      var self = this;

      self.$selector = $('#' + self.selector);
      this._super()
        .observe([
          'active',
          'scriptLoaded',
          'selectedCardType'
        ]);

      // re-init payment method events
      self.$selector.off('changePaymentMethod.' + this.code)
        .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

      // listen block changes
      domObserver.get('#' + self.container, function () {
        if (self.scriptLoaded()) {
          self.$selector.off('submit');
          self.initStripe();
        }
      });

      return this;
    },

    /**
     * Enable/disable current payment method
     * @param {Object} event
     * @param {String} method
     * @returns {exports.changePaymentMethod}
     */
    changePaymentMethod: function (event, method) {
      this.active(method === this.code);

      return this;
    },

    /**
     * Triggered when payment changed
     * @param {Boolean} isActive
     */
    onActiveChange: function (isActive) {
      if (!isActive) {
        this.$selector.off('submitOrder.pmclain_stripe');

        return;
      }
      this.disableEventListeners();
      window.order.addExcludedPaymentMethod(this.code);

      if (!this.publishableKey) {
        this.error($.mage.__('This payment is not available'));

        return;
      }

      this.enableEventListeners();

      if(!this.scriptLoaded()) {
        this.loadScript();
      }
    },

    loadScript: function() {
      var self = this;
      var state = self.scriptLoaded;

      $('body').trigger('processStart');
      require(['https://js.stripe.com/v2/'], function () {
        state(true);
        self.stripe = window.Stripe;
        self.initStripe();
        $('body').trigger('processStop');
      });
    },

    initStripe: function() {
      var self = this;

      try {
        $('body').trigger('processStart');

        self.stripe.setPublishableKey(this.publishableKey);

        $('body').trigger('processStop');
      }catch(e) {
        $('body').trigger('processStop');
        self.error(e.message);
      }
    },

    /**
     * Show alert message
     * @param {String} message
     */
    error: function (message) {
      alert({
        content: message
      });
    },

    /**
     * Enable form event listeners
     */
    enableEventListeners: function () {
      this.$selector.on('submitOrder.pmclain_stripe', this.submitOrder.bind(this));
    },

    /**
     * Disable form event listeners
     */
    disableEventListeners: function () {
      this.$selector.off('submitOrder');
      this.$selector.off('submit');
    },

    /**
     * Trigger order submit
     */
    submitOrder: function () {
      var self = this;
      this.$selector.validate().form();
      this.$selector.trigger('afterValidate.beforeSubmit');

      // validate parent form
      if (this.$selector.validate().errorList.length) {
        $('body').trigger('processStop');
        return false;
      }

      $.when(this.createToken()).done(function() {
        $('body').trigger('processStop');
        $('#' + self.container).find('[type="submit"]').trigger('click');
      }).fail(function(result) {
        $('body').trigger('processStop');
        self.error(result);

        return false;
      });
    },

    /**
     * Convert card information to stripe token
     */
    createToken: function() {
      var self = this;
      var container = $('#' + this.container);

      var cardInfo = {
        number: container.find('#' + this.code + '_cc_number').val(),
        exp_month: container.find('#' + this.code + '_expiration').val(),
        exp_year: container.find('#' + this.code + '_expiration_yr').val(),
        cvc: container.find('#' + this.code + '_cc_cid').val()
      };

      var defer = $.Deferred();

      this.stripe.card.createToken(cardInfo, function(status, response) {
        if (response.error) {
          defer.reject(response.error.message);
        }else {
          container.find('#' + self.code + '_cc_number').val('');
          container.find('#' + self.code + '_cc_last4').val(cardInfo.number.slice(-4));
          delete cardInfo.number;
          $('#' + self.container).find('#' + self.code + '_cc_token').val(response.id);
          defer.resolve();
        }
      });

      return defer.promise();
    },

    /**
     * Place order
     */
    placeOrder: function () {
      $('#' + this.selector).trigger('realOrder');
    },

    /**
     * Get list of currently available card types
     * @returns {Array}
     */
    getCcAvailableTypes: function () {
      var types = [],
        $options = $(this.getSelector('cc_type')).find('option');

      $.map($options, function (option) {
        types.push($(option).val());
      });

      return types;
    },

    /**
     * Get jQuery selector
     * @param {String} field
     * @returns {String}
     */
    getSelector: function (field) {
      return '#' + this.code + '_' + field;
    }
  });
});