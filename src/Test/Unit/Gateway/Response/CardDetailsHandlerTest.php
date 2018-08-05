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

use Pmclain\Stripe\Gateway\Response\CardDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;

class CardDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CardDetailsHandler */
    private $cardHandler;

    /** @var Payment|\PHPUnit_Framework_MockObject_MockObject */
    private $payment;

    /** @var Config|\PHPUnit_Framework_MockObject_MockObject */
    private $config;

    /** @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject */
    private $subjectReader;

    protected function setUp()
    {
        $this->initConfigMock();
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cardHandler = new CardDetailsHandler($this->config,
            $this->subjectReader);
    }

    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
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

        $this->cardHandler->handle($subject, $response);
    }

    private function initConfigMock()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcLast4',
                'setCcExpMonth',
                'setCcExpYear',
                'setCcType',
                'setAdditionalInformation'
            ])
            ->getMock();
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
        $source = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__toArray'])
            ->getMock();
        $source->expects($this->once())
            ->method('__toArray')
            ->willReturn([
                'brand' => 'Visa',
                'last4' => '1234',
                'exp_month' => '01',
                'exp_year' => '18'
            ]);

        $transaction = ['source' => $source];

        return $transaction;
    }
}
