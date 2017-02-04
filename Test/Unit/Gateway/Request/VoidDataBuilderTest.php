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

use Pmclain\Stripe\Gateway\Request\VoidDataBuilder;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class VoidDataBuilderTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var VoidDataBuilder
   */
  private $builder;

  /**
   * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
   */
  private $subjectReader;

  protected function setUp() {
    $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->builder = new VoidDataBuilder($this->subjectReader);
  }

  /**
   *
   * @dataProvider testBuildDataProvider
   */
  public function testBuild($parentTransId, $lastTransId) {
    $paymentDataObject = $this->getMock(PaymentDataObjectInterface::class);
    $buildSubject = ['payment' => $paymentDataObject];
    $paymentMock = $this->getMockBuilder(Payment::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->subjectReader->expects($this->once())
      ->method('readPayment')
      ->with($buildSubject)
      ->willReturn($paymentDataObject);
    $paymentDataObject->expects($this->once())
      ->method('getPayment')
      ->willReturn($paymentMock);
    $paymentMock->expects($this->once())
      ->method('getParentTransactionId')
      ->willReturn($parentTransId);
    if(!$parentTransId) {
      $paymentMock->expects($this->once())
        ->method('getLastTransId')
        ->willReturn($lastTransId);
    }

    $this->assertEquals(
      ['transaction_id' => $parentTransId?:$lastTransId],
      $this->builder->build($buildSubject)
    );
  }

  public function testBuildDataProvider() {
    return [
      ['ch_19RZmz2eZvKYlo2CktQObIT0', null],
      [false, 'ch_19RZmz2eZvKYlo2CktQObIT0']
    ];
  }
}