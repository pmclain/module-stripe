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

use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Refund;
use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;

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
    return Charge::create($attributes);
  }

  public function submitForSettlement($transactionId, $amount = null) {
    $charge = Charge::retrieve($transactionId);
    return $charge->capture(['amount' => $amount]);
  }

  public function void($transactionId) {
    return Refund::create(['charge' => $transactionId]);
  }
}