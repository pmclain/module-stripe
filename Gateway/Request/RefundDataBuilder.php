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

use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pmclain\Stripe\Helper\Payment\Formatter;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;

class RefundDataBuilder implements BuilderInterface {
  use Formatter;

  private $subjectReader;

  public function __construct(
    SubjectReader $subjectReader
  ) {
    $this->subjectReader = $subjectReader;
  }

  public function build(array $subject) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $payment = $paymentDataObject->getPayment();
    $amount = null;

    try {
      $amount = $this->formatPrice($this->subjectReader->readAmount($subject));
    }catch (\InvalidArgumentException $e) {
      //nothing
    }

    $txnId = str_replace(
      '-' . TransactionInterface::TYPE_CAPTURE,
      '',
      $payment->getParentTransactionId()
    );

    return [
      'transaction_id' => $txnId,
      PaymentDataBuilder::AMOUNT => $amount
    ];
  }
}