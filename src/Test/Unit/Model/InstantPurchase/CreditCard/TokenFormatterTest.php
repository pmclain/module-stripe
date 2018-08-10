<?php

namespace Pmclain\Stripe\Test\Unit\Model\InstantPurchase\CreditCard;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use PHPUnit\Framework\TestCase;
use Pmclain\Stripe\Model\InstantPurchase\CreditCard\TokenFormatter;

class TokenFormatterTest extends TestCase
{
    /**
     * @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenMock;

    /**
     * @var TokenFormatter
     */
    private $model;

    public function setUp()
    {
        $this->paymentTokenMock = $this->createMock(PaymentTokenInterface::class);

        $this->model = new TokenFormatter();
    }

    public function testFormatPaymentToken()
    {
        $this->paymentTokenMock->method('getTokenDetails')->willReturn(json_encode([
            'type' => 'AE',
            'maskedCC' => '4444',
            'expirationDate' => '12/29'
        ]));

        $this->assertEquals(
            'Credit Card: American Express, ending: 4444 (expires: 12/29)',
            $this->model->formatPaymentToken($this->paymentTokenMock)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFormatPaymentTokenException()
    {
        $this->model->formatPaymentToken($this->paymentTokenMock);
    }
}
