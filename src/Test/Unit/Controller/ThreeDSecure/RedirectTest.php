<?php

namespace Pmclain\Stripe\Test\Unit\Controller\ThreeDSecure;

use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\Stripe\Controller\ThreeDSecure\Redirect as Controller;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Pmclain\Stripe\Model\Helper\OrderPlace;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote;

class RedirectTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var OrderPlace|MockObject
     */
    private $orderPlaceMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Controller
     */
    private $controller;

    protected function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->orderPlaceMock = $this->createMock(OrderPlace::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactoryMock);

        $this->resultRedirectFactoryMock->method('create')->willReturn($this->resultRedirectMock);

        $this->sessionMock->method('getQuote')->willReturn($this->quoteMock);

        $this->controller = new Controller(
            $this->contextMock,
            $this->sessionMock,
            $this->orderPlaceMock
        );
    }

    public function testExecuteWithInvalidQuote()
    {
        $this->quoteMock->method('getItemsCount')->willReturn(0);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('We can\'t initialize checkout.');

        $this->controller->execute();
    }

    public function testExecutePlaceOrderException()
    {
        $errorMessage = 'Your payment information could not be validated. Please try again.';
        $this->quoteMock->method('getItemsCount')->willReturn(2);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessage);

        $this->orderPlaceMock->method('execute')
            ->willThrowException(new LocalizedException(new \Magento\Framework\Phrase($errorMessage)));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $this->quoteMock->method('getItemsCount')->willReturn(2);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('checkout/onepage/success');

        $this->controller->execute();
    }
}
