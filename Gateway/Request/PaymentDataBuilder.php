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
namespace Pmclain\Stripe\Gateway\Request;

use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Observer\DataAssignObserver;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pmclain\Stripe\Helper\Payment\Formatter;

class PaymentDataBuilder implements BuilderInterface
{
  use Formatter;
  
  const AMOUNT = 'amount';
  const SOURCE = 'source';
  const ORDER_ID = 'description';
  const CURRENCY = 'currency';
  const CAPTURE = 'capture';
  
  private $config;
  private $subjectReader;
  
  public function __construct(
    Config $config,
    SubjectReader $subjectReader
  ) {
    $this->config = $config;
    $this->subjectReader = $subjectReader;
  }
  
  public function build(array $subject) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $payment = $paymentDataObject->getPayment();
    $order = $paymentDataObject->getOrder();
    
    $result = [
      self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($subject)),
      self::ORDER_ID => $order->getOrderIncrementId(),
      self::CURRENCY => $this->config->getCurrency(),
      self::SOURCE => $this->getPaymentSource($payment),
      self::CAPTURE => 'false'
    ];

    return $result;
  }

  protected function getPaymentSource($payment) {
    if($token = $payment->getAdditionalInformation('cc_token')) {
      return $token;
    }
    return [
      'exp_month' => $payment->getCcExpMonth(),
      'exp_year' => $payment->getCcExpYear(),
      'number' => $payment->getCcNumber(),
      'object' => 'card',
      'cvc' => $payment->getCcCid(),
    ];
  }
}