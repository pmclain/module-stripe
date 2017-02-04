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
 */namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Pmclain\Stripe\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Helper\Payment\Formatter;

class PaymentDataBuilderTest extends \PHPUnit_Framework_TestCase
{
  use Formatter;

  /**
   * @var PaymentDataBuilder
   */
  private $builder;

  /**
   * @var Config|\PHPUnit_Framework_MockObject_MockObject
   */
  private $configMock;

  /**
   * @var Payment|\PHPUnit_Framework_MockObject_MockObject
   */
  private $paymentMock;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  private $paymentDataObject;

  /**
   * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
   */
  private $subjectReaderMock;

  /**
   * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  private $orderMock;

  protected function setUp() {
    $this->paymentDataObject = $this->getMock(PaymentDataObjectInterface::class);
    $this->configMock = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
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
    $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->orderMock = $this->getMock(OrderAdapterInterface::class);

    $this->builder = new PaymentDataBuilder($this->configMock, $this->subjectReaderMock);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testBuildReadPaymentException() {
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
  public function testBuildReadAmountException() {
    $buildSubject = [
      'payment' => $this->paymentDataObject,
      'amount' => null
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->with($buildSubject)
      ->willReturn($this->paymentDataObject);
    $this->subjectReaderMock->expects($this->once())
      ->method('readAmount')
      ->willReturn($buildSubject)
      ->willThrowException(new \InvalidArgumentException());

    $this->builder->build($buildSubject);
  }

  public function testBuildWithToken() {
    $expectedResult = [
      PaymentDataBuilder::AMOUNT => $this->formatPrice(10.00),
      PaymentDataBuilder::ORDER_ID => '000000101',
      PaymentDataBuilder::CURRENCY => 'usd',
      PaymentDataBuilder::SOURCE => 'token_number',
      PaymentDataBuilder::CAPTURE => 'false'
    ];

    $buildSubject = [
      'payment' => $this->paymentDataObject,
      'amount' => 10.00
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->willReturn($this->paymentDataObject);
    $this->subjectReaderMock->expects($this->once())
      ->method('readAmount')
      ->willReturn(10.00);
    $this->paymentMock->expects($this->once())
      ->method('getAdditionalInformation')
      ->with('cc_token')
      ->willReturn('token_number');
    $this->paymentDataObject->expects($this->once())
      ->method('getPayment')
      ->willReturn($this->paymentMock);
    $this->paymentDataObject->expects($this->once())
      ->method('getOrder')
      ->willReturn($this->orderMock);
    $this->orderMock->expects($this->once())
      ->method('getOrderIncrementId')
      ->willReturn('000000101');

    $this->assertEquals(
      $expectedResult,
      $this->builder->build($buildSubject)
    );
  }

  public function testBuildWithCardData() {
    $expectedResult = [
      PaymentDataBuilder::AMOUNT => $this->formatPrice(10.00),
      PaymentDataBuilder::ORDER_ID => '000000101',
      PaymentDataBuilder::CURRENCY => 'usd',
      PaymentDataBuilder::SOURCE => [
        'exp_month' => '01',
        'exp_year' => '18',
        'number' => '4111111111111111',
        'object' => 'card',
        'cvc' => '123'
      ],
      PaymentDataBuilder::CAPTURE => 'false'
    ];

    $buildSubject = [
      'payment' => $this->paymentDataObject,
      'amount' => 10.00
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->willReturn($this->paymentDataObject);
    $this->subjectReaderMock->expects($this->once())
      ->method('readAmount')
      ->willReturn(10.00);
    $this->paymentMock->expects($this->once())
      ->method('getCcNumber')
      ->willReturn('4111111111111111');
    $this->paymentMock->expects($this->once())
      ->method('getCcExpMonth')
      ->willReturn('01');
    $this->paymentMock->expects($this->once())
      ->method('getCcExpYear')
      ->willReturn('18');
    $this->paymentMock->expects($this->once())
      ->method('getCcCid')
      ->willReturn('123');
    $this->paymentMock->expects($this->once())
      ->method('getAdditionalInformation')
      ->with('cc_token')
      ->willReturn(null);
    $this->paymentDataObject->expects($this->once())
      ->method('getPayment')
      ->willReturn($this->paymentMock);
    $this->paymentDataObject->expects($this->once())
      ->method('getOrder')
      ->willReturn($this->orderMock);
    $this->orderMock->expects($this->once())
      ->method('getOrderIncrementId')
      ->willReturn('000000101');

    $this->assertEquals(
      $expectedResult,
      $this->builder->build($buildSubject)
    );
  }
}