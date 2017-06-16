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
 */
namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use Pmclain\Stripe\Gateway\Config\Config;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Helper\Payment\Formatter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;

class PaymentDataBuilderTest extends \PHPUnit_Framework_TestCase
{
  use Formatter;

  /**
   * @var PaymentDataBuilder
   */
  private $builder;

  /**
   * @var Config|MockObject
   */
  private $configMock;

  /**
   * @var Payment|MockObject
   */
  private $paymentMock;

  /**
   * @var PaymentDataObjectInterface|MockObject
   */
  private $paymentDataObjectMock;

  /**
   * @var SubjectReader|MockObject
   */
  private $subjectReaderMock;

  /** @var  Session|MockObject */
  private $customerSessionMock;

  /** @var  CustomerRepositoryInterface|MockObject */
  private $customerRespositoryMock;

  /** @var CustomerInterface|MockObject */
  private $customerInterfaceMock;

  /**
   * @var OrderAdapterInterface|MockObject
   */
  private $orderMock;

  /** @var AttributeInterface|MockObject */
  private $attributeInterfaceMock;

  protected function setUp() {
    $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

    $this->paymentDataObjectMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['getPayment'])
      ->getMockForAbstractClass();

    $this->configMock = $this->getMockBuilder(Config::class)
      ->disableOriginalConstructor()
      ->setMethods(['getCurrency'])
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

    $this->customerSessionMock = $this->getMockBuilder(Session::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->customerRespositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
      ->getMockForAbstractClass();

    $this->customerInterfaceMock = $this->getMockBuilder(CustomerInterface::class)
      ->getMockForAbstractClass();

    $this->attributeInterfaceMock = $this->getMockBuilder(AttributeInterface::class)
      ->getMockForAbstractClass();

    $this->orderMock = $this->getMock(OrderAdapterInterface::class);

    $this->builder = $objectManager->getObject(
      PaymentDataBuilder::class,
      [
        'subjectReader' => $this->subjectReaderMock,
        'config' => $this->configMock,
        'customerRepository' => $this->customerRespositoryMock,
        'customerSession' => $this->customerSessionMock
      ]
    );
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
      'payment' => $this->paymentDataObjectMock,
      'amount' => null
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->with($buildSubject)
      ->willReturn($this->paymentDataObjectMock);
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
      PaymentDataBuilder::CURRENCY => 'USD',
      PaymentDataBuilder::SOURCE => 'token_number',
      PaymentDataBuilder::CAPTURE => 'false'
    ];

    $buildSubject = [
      'payment' => $this->paymentDataObjectMock,
      'amount' => 10.00
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->willReturn($this->paymentDataObjectMock);

    $this->subjectReaderMock->expects($this->once())
      ->method('readAmount')
      ->willReturn(10.00);

    $this->paymentMock->expects($this->at(0))
      ->method('getAdditionalInformation')
      ->with('cc_token')
      ->willReturn('token_number');

    $this->paymentMock->expects($this->at(1))
      ->method('getAdditionalInformation')
      ->with('is_active_payment_token_enabler')
      ->willReturn(false);

    $this->paymentDataObjectMock->expects($this->any())
      ->method('getPayment')
      ->willReturn($this->paymentMock);

    $this->paymentDataObjectMock->expects($this->once())
      ->method('getOrder')
      ->willReturn($this->orderMock);

    $this->orderMock->expects($this->once())
      ->method('getOrderIncrementId')
      ->willReturn('000000101');

    $this->configMock->expects($this->once())
      ->method('getCurrency')
      ->willReturn('USD');

    $this->assertEquals(
      $expectedResult,
      $this->builder->build($buildSubject)
    );
  }

  public function testBuildWithCardData() {
    $expectedResult = [
      PaymentDataBuilder::AMOUNT => $this->formatPrice(10.00),
      PaymentDataBuilder::ORDER_ID => '000000101',
      PaymentDataBuilder::CURRENCY => 'USD',
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
      'payment' => $this->paymentDataObjectMock,
      'amount' => 10.00
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->willReturn($this->paymentDataObjectMock);

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

    $this->paymentMock->expects($this->at(0))
      ->method('getAdditionalInformation')
      ->with('cc_token')
      ->willReturn(null);

    $this->paymentDataObjectMock->expects($this->any())
      ->method('getPayment')
      ->willReturn($this->paymentMock);

    $this->paymentDataObjectMock->expects($this->once())
      ->method('getOrder')
      ->willReturn($this->orderMock);

    $this->orderMock->expects($this->once())
      ->method('getOrderIncrementId')
      ->willReturn('000000101');

    $this->configMock->expects($this->once())
      ->method('getCurrency')
      ->willReturn('USD');

    $this->assertEquals(
      $expectedResult,
      $this->builder->build($buildSubject)
    );
  }

  public function testBuildWithSavePayment() {
    $expectedResult = [
      PaymentDataBuilder::AMOUNT => $this->formatPrice(10.00),
      PaymentDataBuilder::ORDER_ID => '000000101',
      PaymentDataBuilder::CURRENCY => 'USD',
      PaymentDataBuilder::SOURCE => 'token_number',
      PaymentDataBuilder::CAPTURE => 'false',
      PaymentDataBuilder::CUSTOMER => 'cus_token',
      PaymentDataBuilder::SAVE_IN_VAULT => true
    ];

    $buildSubject = [
      'payment' => $this->paymentDataObjectMock,
      'amount' => 10.00
    ];

    $this->subjectReaderMock->expects($this->once())
      ->method('readPayment')
      ->with($buildSubject)
      ->willReturn($this->paymentDataObjectMock);

    $this->paymentDataObjectMock->expects($this->once())
      ->method('getPayment')
      ->willReturn($this->paymentMock);

    $this->paymentDataObjectMock->expects($this->once())
      ->method('getOrder')
      ->willReturn($this->orderMock);

    $this->subjectReaderMock->expects($this->once())
      ->method('readAmount')
      ->willReturn(10.00);

    $this->orderMock->expects($this->once())
      ->method('getOrderIncrementId')
      ->willReturn('000000101');

    $this->configMock->expects($this->once())
      ->method('getCurrency')
      ->willReturn('USD');

    $this->paymentMock->expects($this->at(0))
      ->method('getAdditionalInformation')
      ->with('cc_token')
      ->willReturn('token_number');

    $this->paymentMock->expects($this->at(1))
      ->method('getAdditionalInformation')
      ->with('is_active_payment_token_enabler')
      ->willReturn(true);

    $this->customerRespositoryMock->expects($this->once())
      ->method('getById')
      ->willReturn($this->customerInterfaceMock);

    $this->customerSessionMock->expects($this->once())
      ->method('getCustomerId')
      ->willReturn(1);

    $this->customerInterfaceMock->expects($this->once())
      ->method('getCustomAttribute')
      ->willReturn($this->attributeInterfaceMock);

    $this->attributeInterfaceMock->expects($this->once())
      ->method('getValue')
      ->willReturn('cus_token');

    $this->assertEquals(
      $expectedResult,
      $this->builder->build($buildSubject)
    );
  }
}