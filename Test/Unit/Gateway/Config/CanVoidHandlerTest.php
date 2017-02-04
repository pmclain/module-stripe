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
 */namespace Pmclain\Stripe\Test\Unit\Gateway\Config;

use Pmclain\Stripe\Gateway\Config\CanVoidHandler;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandlerTest extends \PHPUnit_Framework_TestCase
{
  public function testHandleNotOrderPayment() {
    $paymentDataObject = $this->getMock(PaymentDataObjectInterface::class);
    $subject = ['payment' => $paymentDataObject];

    $subjectReader = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();

    $subjectReader->expects(static::once())
      ->method('readPayment')
      ->willReturn($paymentDataObject);

    $paymentMock = $this->getMock(InfoInterface::class);

    $paymentDataObject->expects(static::once())
      ->method('getPayment')
      ->willReturn($paymentMock);

    $voidHandler = new CanVoidHandler($subjectReader);

    $this->assertFalse($voidHandler->handle($subject));
  }

  public function testHandleSomeAmountWasPaid() {
    $paymentDataObject = $this->getMock(PaymentDataObjectInterface::class);
    $subject = ['payment' => $paymentDataObject];

    $subjectReader = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();

    $subjectReader->expects(static::once())
      ->method('readPayment')
      ->willReturn($paymentDataObject);

    $paymentMock = $this->getMockBuilder(Payment::class)
      ->disableOriginalConstructor()
      ->getMock();

    $paymentDataObject->expects(static::once())
      ->method('getPayment')
      ->willReturn($paymentMock);

    $paymentMock->expects(static::once())
      ->method('getAmountPaid')
      ->willReturn(1.00);

    $voidHandler = new CanVoidHandler($subjectReader);

    $this->assertFalse($voidHandler->handle($subject));
  }
}