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

namespace Pmclain\Stripe\Test\Unit\Gateway\Response;

use Pmclain\Stripe\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;

class PaymentDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TRANSACTION_ID = 'txn_19PbvF2eZvKYlo2C0HCaOJw2';

    /** @var PaymentDetailsHandler */
    private $paymentHandler;

    /** @var Payment|\PHPUnit_Framework_MockObject_MockObject */
    private $payment;

    /** @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject */
    private $subjectReader;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation'
            ])
            ->getMock();
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects($this->once())
            ->method('setCcTransId');
        $this->payment->expects($this->once())
            ->method('setLastTransId');
        $this->payment->expects($this->any())
            ->method('setAdditionalInformation');

        $this->paymentHandler = new PaymentDetailsHandler($this->subjectReader);
    }

    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObject();
        $transaction = $this->getStripeTransaction();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $transaction];

        $this->subjectReader->expects($this->once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        $this->subjectReader->expects($this->once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->paymentHandler->handle($subject, $response);
    }

    private function getPaymentDataObject()
    {
        $paymentDataObject = $this->getMockBuilder(PaymentDataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMock();

        $paymentDataObject->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $paymentDataObject;
    }

    private function getStripeTransaction()
    {
        $outcome = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__toArray'])
            ->getMock();
        $outcome->expects($this->once())
            ->method('__toArray')
            ->willReturn([
                PaymentDetailsHandler::RISK_LEVEL => 'normal',
                PaymentDetailsHandler::SELLER_MESSAGE => '',
                PaymentDetailsHandler::CAPTURE => '',
                PaymentDetailsHandler::TYPE => 'authorized'
            ]);

        $transaction = [
            'id' => 'ch_19RiLp2eZvKYlo2CjlqQlezC',
            'outcome' => $outcome
        ];

        return $transaction;
    }
}
