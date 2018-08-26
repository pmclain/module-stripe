<?php

namespace Pmclain\Stripe\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Pmclain\Stripe\Model\StripeCustomerManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use Pmclain\Stripe\Model\Adapter\StripeAdapter;

class StripeCustomerManagementTest extends TestCase
{
    /**
     * @var StripeCustomerManagement
     */
    private $stripeCustomerManagement;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var StripeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMock;

    /**
     * @var AttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAttributeMock;

    protected function setUp()
    {
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);
        $this->customerAttributeMock = $this->createMock(AttributeInterface::class);
        $this->adapterMock = $this->createMock(StripeAdapter::class);

        $this->customerRepositoryMock->method('getById')
            ->willReturn($this->customerMock);

        $this->stripeCustomerManagement = new StripeCustomerManagement(
            $this->adapterMock,
            $this->customerRepositoryMock
        );
    }

    public function testGetStripeCustomerId()
    {
        $customerToken = 'cus_token';
        $this->customerAttributeMock->method('getValue')
            ->willReturn($customerToken);

        $this->customerMock->method('getCustomAttribute')
            ->willReturn($this->customerAttributeMock);

        $this->assertEquals($customerToken, $this->stripeCustomerManagement->getStripeCustomerId(1));
    }

    public function testCreateStripeCustomer()
    {
        $customerToken = 'cus_token';

        $this->customerMock->method('getEmail')
            ->willReturn('test@test.test');

        $stripeCustomer = new \stdClass();
        $stripeCustomer->id = $customerToken;
        $this->adapterMock->method('createCustomer')
            ->willReturn($stripeCustomer);

        $this->customerMock->expects($this->once())
            ->method('setCustomAttribute')
            ->with('stripe_customer_id', $customerToken)
            ->willReturnSelf();

        $this->customerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->customerMock);

        $this->assertEquals($customerToken, $this->stripeCustomerManagement->getStripeCustomerId(1));

    }
}
