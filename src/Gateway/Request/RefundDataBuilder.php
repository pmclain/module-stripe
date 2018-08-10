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

namespace Pmclain\Stripe\Gateway\Request;

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;

class RefundDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * RefundDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        SubjectReader $subjectReader,
        PriceFormatter $priceFormatter
    ) {
        $this->subjectReader = $subjectReader;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();
        $amount = null;

        try {
            $amount = $this->priceFormatter->formatPrice($this->subjectReader->readAmount($subject));
        } catch (\InvalidArgumentException $e) {
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
