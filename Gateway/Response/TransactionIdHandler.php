<?php
/**
 * Pmclain_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Pmclain
 * @package   Pmclain_Stripe
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */

namespace Pmclain\Stripe\Gateway\Response;

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class TransactionIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransactionIdHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $subject
     * @param array $response
     */
    public function handle(array $subject, array $response)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);

        if ($paymentDataObject->getPayment() instanceof Payment) {
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

    /**
     * @param Payment $orderPayment
     * @param $transaction
     */
    protected function setTransactionId(Payment $orderPayment, $transaction)
    {
        $orderPayment->setTransactionId($transaction['id']);
    }

    /**
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * @param Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}
