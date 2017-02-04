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
namespace Pmclain\Stripe\Gateway\Config;

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandler implements ValueHandlerInterface
{
  private $subjectReader;

  public function __construct(
    SubjectReader $subjectReader
  ) {
    $this->subjectReader = $subjectReader;
  }

  public function handle(array $subject, $storeId = NULL) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $payment = $paymentDataObject->getPayment();

    return $payment instanceof Payment && !(bool)$payment->getAmountPaid();
  }
}