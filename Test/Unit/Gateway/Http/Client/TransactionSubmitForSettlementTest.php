<?php
/**
 * Pmclain_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category  Pmclain
 * @package   Pmclain_Stripe
 * @copyright Copyright (c) 2017
 * @license   https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */namespace Pmclain\Stripe\Test\Unit\Gateway\Http\Client;

use Stripe\Charge;
use Pmclain\Stripe\Gateway\Http\Client\TransactionSubmitForSettlement;
use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

class TransactionSubmitForSettlementTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var TransactionSubmitForSettlement
   */
  private $client;

  /**
   * @var Logger|\PHPUnit_Framework_MockObject_MockObject
   */
  private $logger;

  /**
   * @var StripeAdapter|\PHPUnit_Framework_MockObject_MockObject
   */
  private $adapter;

  protected function setUp() {
    $criticalLoggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
    $this->logger = $this->getMockBuilder(Logger::class)
      ->disableOriginalConstructor()
      ->setMethods(['debug'])
      ->getMock();
    $this->adapter = $this->getMockBuilder(StripeAdapter::class)
      ->disableOriginalConstructor()
      ->setMethods(['submitForSettlement'])
      ->getMock();

    $this->client = new TransactionSubmitForSettlement(
      $criticalLoggerMock,
      $this->logger,
      $this->adapter
    );
  }

  /**
   * @expectedException \Magento\Payment\Gateway\Http\ClientException
   * @expectedExceptionMessage Transaction has been declined
   */
  public function testPlaceRequestWithException() {
    $exception = new \Exception('Transaction has been declined');
    $this->adapter->expects($this->once())
      ->method('submitForSettlement')
      ->willThrowException($exception);

    $tranferObjectMock = $this->getTransferObjectMock();
    $this->client->placeRequest($tranferObjectMock);
  }

  public function testPlaceRequest() {
    $data = new Charge();
    $this->adapter->expects($this->once())
      ->method('submitForSettlement')
      ->willReturn($data);

    $transferObjectMock = $this->getTransferObjectMock();
    $response = $this->client->placeRequest($transferObjectMock);

    $this->assertTrue(is_object($response['object']));
    $this->assertEquals(['object' => $data], $response);
  }
  
  private function getTransferObjectMock() {
    $mock = $this->getMock(TransferInterface::class);
    $mock->expects($this->once())
      ->method('getBody')
      ->willReturn(
        [
          'transaction_id' => 'ch_19RXyy2eZvKYlo2CNU3GxOOe',
          'amount' => 1.00
        ]
      );

    return $mock;
  }
}