<?php
/**
 * Pmclain_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category  Pmclain
 * @package   Pmclain_Stripe
 * @copyright Copyright (c) 2017
 * @license   https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */

namespace Pmclain\Stripe\Model\Adapter;

use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Refund;
use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;

class StripeAdapter
{
  private $config;

  protected $encryptor;

  public function __construct(
    Config $config,
    EncryptorInterface $encryptorInterface
  ) {
    $this->encryptor = $encryptorInterface;
    $this->config = $config;
    $this->initCredentials();
  }

  protected function initCredentials() {
    Stripe::setApiKey($this->encryptor->decrypt($this->config->getSecretKey()));
  }

  public function refund($transactionId, $amount = null) {
    return Refund::create([
      'charge' => $transactionId,
      'amount' => $amount
    ]);
  }

  public function sale($attributes) {
    if(isset($attributes[PaymentDataBuilder::SAVE_IN_VAULT])) {
      unset($attributes[PaymentDataBuilder::SAVE_IN_VAULT]);
      $attributes = $this->_saveCustomerCard($attributes);

      if($attributes instanceof \Stripe\Error\Card) {
        return $attributes;
      }
    }
    try {
      return Charge::create($attributes);
    }catch (\Stripe\Error\Card $e) {
      return $e;
    }
  }

  public function submitForSettlement($transactionId, $amount = null) {
    $charge = Charge::retrieve($transactionId);
    return $charge->capture(['amount' => $amount]);
  }

  public function void($transactionId) {
    return Refund::create(['charge' => $transactionId]);
  }

  /**
   * @param $attributes
   * @return \Exception|\Stripe\Error\Card|array
   * @throws \Magento\Framework\Validator\Exception
   */
  protected function _saveCustomerCard($attributes) {
    try {
      $stripeCustomer = Customer::retrieve($attributes[PaymentDataBuilder::CUSTOMER]);

      $card = $stripeCustomer->sources->create([
        'source' => $attributes[PaymentDataBuilder::SOURCE]
      ]);

      $attributes[PaymentDataBuilder::SOURCE] = $card->id;

      return $attributes;
    }catch (\Stripe\Error\Card $e) {
      return $e;
    }catch (\Exception $e) {
      throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
    }
  }
}