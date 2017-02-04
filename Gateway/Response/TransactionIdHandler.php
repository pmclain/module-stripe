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
namespace Pmclain\Stripe\Gateway\Response;

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class TransactionIdHandler implements HandlerInterface
{
  private $subjectReader;

  public function __construct(
    SubjectReader $subjectReader
  ) {
    $this->subjectReader = $subjectReader;
  }

  public function handle(array $subject, array $response) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);

    if($paymentDataObject->getPayment() instanceof Payment) {
      $transaction = $this->subjectReader->readTransaction($response);
      $orderPayment = $paymentDataObject->getPayment();

      $this->setTransactionId(
        $orderPayment,
        $transaction
      );

      $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
      $closed = $this->shouldCloseParentTransaction($orderPayment);
      $orderPayment->setShouldCloseParentTransaction($closed);
    }
  }

  protected function setTransactionId(Payment $orderPayment, $transaction) {
    $orderPayment->setTransactionId($transaction['id']);
  }

  protected function shouldCloseTransaction() {
    return false;
  }

  protected function shouldCloseParentTransaction(Payment $orderPayment) {
    return false;
  }
}