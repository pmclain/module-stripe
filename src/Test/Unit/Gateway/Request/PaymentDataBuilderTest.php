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

namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use Pmclain\Stripe\Api\StripeCustomerManagementInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeInterface;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;

class PaymentDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataBuilder
     */
    private $builder;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /** @var  Session|MockObject */
    private $customerSessionMock;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @var StripeCustomerManagementInterface|MockObject
     */
    private $stripeCustomerManagementMock;

    protected function setUp()
    {
        $this->paymentDataObjectMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment'])
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrency'])
            ->getMock();

        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAdditionalInformation',
                'getCcNumber',
                'getCcExpMonth',
                'getCcExpYear',
                'getCcCid'
            ])
            ->getMock();

        $this->subjectReaderMock = $this->createMock(SubjectReader::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->stripeCustomerManagementMock = $this->createMock(StripeCustomerManagementInterface::class);

        $this->configMock->method('getCurrency')->willReturn('USD');

        $priceFormatter = new PriceFormatter($this->configMock);

        $this->builder = new PaymentDataBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            $this->customerSessionMock,
            $priceFormatter,
            $this->stripeCustomerManagementMock
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [];

        $this->subjectReaderMock->expects($this->once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        $this->builder->build($buildSubject);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadAmountException()
    {
        $buildSubject = [
            'payment' => $this->paymentDataObjectMock,
            'amount' => null
        ];

        $this->subjectReaderMock->expects($this->once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDataObjectMock);
        $this->subjectReaderMock->expects($this->once())
            ->method('readAmount')
            ->willReturn($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        $this->builder->build($buildSubject);
    }

    public function testBuildWithToken()
    {
        $expectedResult = [
            PaymentDataBuilder::AMOUNT => '1000',
            PaymentDataBuilder::ORDER_ID => '000000101',
            PaymentDataBuilder::CURRENCY => 'USD',
            PaymentDataBuilder::SOURCE => 'token_number',
            PaymentDataBuilder::CAPTURE => 'false'
        ];

        $buildSubject = [
            'payment' => $this->paymentDataObjectMock,
            'amount' => 10.00
        ];

        $this->subjectReaderMock->method('readPayment')
            ->willReturn($this->paymentDataObjectMock);

        $this->subjectReaderMock->method('readAmount')
            ->willReturn(10.00);

        $this->paymentMock->expects($this->at(0))
            ->method('getAdditionalInformation')
            ->with('cc_token')
            ->willReturn('token_number');

        $this->paymentMock->expects($this->at(1))
            ->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(false);

        $this->paymentDataObjectMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentDataObjectMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->method('getOrderIncrementId')
            ->willReturn('000000101');

        $this->assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }

    public function testBuildWithSavePayment()
    {
        $expectedResult = [
            PaymentDataBuilder::AMOUNT => '1000',
            PaymentDataBuilder::ORDER_ID => '000000101',
            PaymentDataBuilder::CURRENCY => 'USD',
            PaymentDataBuilder::SOURCE => 'token_number',
            PaymentDataBuilder::CAPTURE => 'false',
            PaymentDataBuilder::CUSTOMER => 'cus_token',
        ];

        $buildSubject = [
            'payment' => $this->paymentDataObjectMock,
            'amount' => 10.00
        ];

        $this->subjectReaderMock->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDataObjectMock);

        $this->paymentDataObjectMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentDataObjectMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->subjectReaderMock->method('readAmount')
            ->willReturn(10.00);

        $this->orderMock->method('getOrderIncrementId')
            ->willReturn('000000101');

        $this->paymentMock->expects($this->at(0))
            ->method('getAdditionalInformation')
            ->with('cc_token')
            ->willReturn('token_number');

        $this->paymentMock->expects($this->at(1))
            ->method('getAdditionalInformation')
            ->with('is_active_payment_token_enabler')
            ->willReturn(true);

        $this->stripeCustomerManagementMock->method('getStripeCustomerId')
            ->willReturn('cus_token');

        $this->stripeCustomerManagementMock->method('addCustomerCard')
            ->willReturn('token_number');

        $this->assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
