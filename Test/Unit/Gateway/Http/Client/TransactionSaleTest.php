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

namespace Pmclain\Stripe\Test\Unit\Gateway\Http\Client;

use Pmclain\Stripe\Gateway\Http\Client\TransactionSale;
use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

class TransactionSaleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransactionSale
     */
    private $model;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var StripeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    protected function setUp()
    {
        $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(StripeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new TransactionSale(
            $criticalLoggerMock,
            $this->loggerMock,
            $this->adapter
        );
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Http\ClientException
     * @expectedExceptionMessage Test message
     */
    public function testPlaceRequestException()
    {
        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionSale::class,
                    'response' => []
                ]
            );

        $this->adapter->expects($this->once())
            ->method('sale')
            ->willThrowException(new \Exception('Test message'));

        $transferObjectMock = $this->getTransferObjectMock();

        $this->model->placeRequest($transferObjectMock);
    }

    public function testPlaceRequestSuccess()
    {
        $response = $this->getResponseObject();
        $this->adapter->expects($this->once())
            ->method('sale')
            ->willReturn($response);

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                [
                    'request' => $this->getTransferData(),
                    'client' => TransactionSale::class,
                    'response' => ['success' => 1]
                ]
            );

        $actualResult = $this->model->placeRequest($this->getTransferObjectMock());

        $this->assertEquals(['object' => $response], $actualResult);
    }

    private function getTransferData()
    {
        return ['test-data-key' => 'test-data-value'];
    }

    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    private function getResponseObject()
    {
        return ['success' => true];
    }
}
