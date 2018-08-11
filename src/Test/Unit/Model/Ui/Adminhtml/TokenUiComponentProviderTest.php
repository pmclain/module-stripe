<?php

namespace Pmclain\Stripe\Test\Unit\Model\Ui\Adminhtml;

use PHPUnit\Framework\TestCase;
use Pmclain\Stripe\Model\Ui\Adminhtml\TokenUiComponentProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\Json\Decoder;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class TokenUiComponentProviderTest extends TestCase
{
    /**
     * @var TokenUiComponentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentFactoryMock;

    /**
     * @var TokenUiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenMock;

    /**
     * @var TokenUiComponentProvider
     */
    private $model;

    protected function setUp()
    {
        $this->componentFactoryMock = $this->createMock(TokenUiComponentInterfaceFactory::class);
        $this->componentMock = $this->createMock(TokenUiComponentInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->paymentTokenMock = $this->createMock(PaymentTokenInterface::class);

        $this->componentFactoryMock->method('create')->willReturn($this->componentMock);

        $this->model = new TokenUiComponentProvider(
            $this->componentFactoryMock,
            $this->urlMock,
            new Decoder()
        );
    }

    public function testGetComponentForToken()
    {
        $this->assertInstanceOf(
            TokenUiComponentInterface::class,
            $this->model->getComponentForToken($this->paymentTokenMock)
        );
    }
}
