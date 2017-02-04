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

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Pmclain\Stripe\Gateway\Request\RefundDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Helper\Payment\Formatter;

class RefundDataBuilderTest extends \PHPUnit_Framework_TestCase
{
  use Formatter;

  /**
   * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
   */
  private $subjectReader;

  /**
   * @var RefundDataBuilder
   */
  private $dataBuilder;

  public function setUp() {
    $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->dataBuilder = new RefundDataBuilder($this->subjectReader);
  }

  public function testBuild() {
    $paymentDataObject = $this->getMock(PaymentDataObjectInterface::class);
    $paymentMock = $this->getMockBuilder(Payment::class)
      ->disableOriginalConstructor()
      ->getMock();

    $buildSubject = [
      'payment' => $paymentDataObject,
      'amount' => 10.00,
    ];
    $transactionId = 'ch_19RZmz2eZvKYlo2CktQObIT0';

    $this->subjectReader->expects($this->once())
      ->method('readPayment')
      ->with($buildSubject)
      ->willReturn($paymentDataObject);
    $paymentDataObject->expects($this->once())
      ->method('getPayment')
      ->willReturn($paymentMock);
    $paymentMock->expects($this->once())
      ->method('getParentTransactionId')
      ->willReturn($transactionId);
    $this->subjectReader->expects($this->once())
      ->method('readAmount')
      ->willReturn($buildSubject)
      ->willReturn($buildSubject['amount']);

    $this->assertEquals(
      ['transaction_id' => $transactionId, PaymentDataBuilder::AMOUNT => $this->formatPrice(10.00)],
      $this->dataBuilder->build($buildSubject)
    );
  }

  public function testBuildNullAmount() {
    $paymentDataObject = $this->getMock(PaymentDataObjectInterface::class);
    $paymentMock = $this->getMockBuilder(Payment::class)
      ->disableOriginalConstructor()
      ->getMock();

    $buildSubject = ['payment' => $paymentDataObject];
    $transactionId = 'ch_19RZmz2eZvKYlo2CktQObIT0';

    $this->subjectReader->expects($this->once())
      ->method('readPayment')
      ->with($buildSubject)
      ->willReturn($paymentDataObject);
    $paymentDataObject->expects($this->once())
      ->method('getPayment')
      ->willReturn($paymentMock);
    $paymentMock->expects($this->once())
      ->method('getParentTransactionId')
      ->willReturn($transactionId);
    $this->subjectReader->expects($this->once())
      ->method('readAmount')
      ->with($buildSubject)
      ->willThrowException(new \InvalidArgumentException());

    $this->assertEquals(
      ['transaction_id' => $transactionId, PaymentDataBuilder::AMOUNT => null],
      $this->dataBuilder->build($buildSubject)
    );
  }
}