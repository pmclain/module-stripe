<?php

namespace Pmclain\Stripe\Test\Unit\Model\Helper;

use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use PHPUnit\Framework\TestCase;
use Pmclain\Stripe\Model\Helper\OrderPlace;
use Magento\Checkout\Helper\Data;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Address;

class OrderPlaceTest extends TestCase
{
    const QUOTE_ID = 33;

    /**
     * @var CartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagementMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutHelperMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMock;

    /**
     * @var OrderPlace
     */
    private $model;

    protected function setUp()
    {
        $this->cartManagementMock = $this->createMock(CartManagementInterface::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->checkoutHelperMock = $this->createMock(Data::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomerId', 'getPayment', 'getBillingAddress', 'getId', 'setCustomerEmail', 'setCustomerGroupId', 'collectTotals'])
            ->getMock();
        $this->paymentMock = $this->createMock(Payment::class);
        $this->addressMock = $this->createMock(Address::class);

        $this->quoteMock->method('getPayment')->willReturn($this->paymentMock);
        $this->quoteMock->method('getBillingAddress')->willReturn($this->addressMock);
        $this->quoteMock->method('getId')->willReturn(self::QUOTE_ID);

        $this->model = new OrderPlace(
            $this->cartManagementMock,
            $this->customerSessionMock,
            $this->checkoutHelperMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteInvalidReturn()
    {
        $clientSecret = '3ds_client_secret';
        $paymentSrc = 'src_123';

        $this->paymentMock->method('getAdditionalInformation')
            ->will($this->returnCallback(function  ($arg) use ($paymentSrc) {
                if ($arg === 'three_d_client_secret') {
                    return 'wrong_secret';
                } elseif ($arg === 'three_d_src') {
                    return $paymentSrc;
                }
                throw new \InvalidArgumentException("Argument $arg is not supported.");
            }));

        $this->model->execute($this->quoteMock, $paymentSrc, $clientSecret);
    }

    public function testExecuteAsGuest()
    {
        $clientSecret = '3ds_client_secret';
        $paymentSrc = 'src_123';

        $this->paymentMock->method('getAdditionalInformation')
            ->will($this->returnCallback(function  ($arg) use ($paymentSrc, $clientSecret) {
                if ($arg === 'three_d_client_secret') {
                    return $clientSecret;
                } elseif ($arg === 'three_d_src') {
                    return $paymentSrc;
                }
                throw new \InvalidArgumentException("Argument $arg is not supported.");
            }));

        $this->checkoutHelperMock->method('isAllowedGuestCheckout')
            ->willReturn(true);

        $this->quoteMock->method('setCustomerId')->willReturnSelf();
        $this->quoteMock->method('setCustomerEmail')->willReturnSelf();
        $this->quoteMock->method('setCustomerGroupId')->willReturnSelf();

        $this->cartManagementMock->expects($this->once())
            ->method('placeOrder')
            ->with(self::QUOTE_ID);

        $this->model->execute($this->quoteMock, $paymentSrc, $clientSecret);
    }
}
