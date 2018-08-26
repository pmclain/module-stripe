<?php

namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Pmclain\Stripe\Gateway\Request\VaultPaymentDataBuilder;
use Pmclain\Stripe\Api\StripeCustomerManagementInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Customer\Model\Session;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Vault\Model\PaymentToken;

class VaultPaymentDataBuilderTest extends TestCase
{
    /**
     * @var VaultPaymentDataBuilder
     */
    private $builder;

    /**
     * @var StripeCustomerManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stripeCustomerManagementMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var OrderPaymentExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentExtensionAttributesMock;

    /**
     * @var PaymentToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenMock;

    protected function setUp()
    {
        $this->stripeCustomerManagementMock = $this->createMock(StripeCustomerManagementInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->paymentExtensionAttributesMock = $this->getMockBuilder(OrderPaymentExtension::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVaultPaymentToken'])
            ->getMock();
        $this->paymentTokenMock = $this->createMock(PaymentToken::class);

        $this->orderMock->method('getOrderIncrementId')
            ->willReturn('100000001');

        $this->configMock->method('getCurrency')
            ->willReturn('USD');

        $this->paymentTokenMock->method('getGatewayToken')
            ->willReturn('src_token');

        $this->paymentExtensionAttributesMock->method('getVaultPaymentToken')
            ->willReturn($this->paymentTokenMock);

        $this->paymentMock->method('getExtensionAttributes')
            ->willReturn($this->paymentExtensionAttributesMock);

        $this->paymentDataObjectMock->method('getOrder')
            ->willReturn($this->orderMock);
        $this->paymentDataObjectMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->stripeCustomerManagementMock->method('getStripeCustomerId')
            ->willReturn('cus_token');

        $this->builder = new VaultPaymentDataBuilder(
            $this->configMock,
            new SubjectReader(),
            $this->sessionMock,
            new PriceFormatter($this->configMock),
            $this->stripeCustomerManagementMock
        );
    }

    public function testBuild()
    {
        $subject = [
            'amount' => 10.00,
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->assertEquals(
            [
                VaultPaymentDataBuilder::AMOUNT => '1000',
                VaultPaymentDataBuilder::ORDER_ID => '100000001',
                VaultPaymentDataBuilder::CURRENCY => 'USD',
                VaultPaymentDataBuilder::SOURCE => 'src_token',
                VaultPaymentDataBuilder::CAPTURE => 'false',
                VaultPaymentDataBuilder::CUSTOMER => 'cus_token',
            ],
            $this->builder->build($subject)
        );
    }
}
