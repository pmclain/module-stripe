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

namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use Pmclain\Stripe\Gateway\Request\CaptureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;
use Pmclain\Stripe\Gateway\Config\Config;

class CaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Pmclain\Stripe\Gateway\Request\CaptureDataBuilder
     */
    private $builder;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObject;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->paymentDataObject = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->createMock(Payment::class);
        $this->subjectReaderMock = $this->createMock(SubjectReader::class);

        $configMock = $this->createMock(Config::class);
        $configMock->method('getCurrency')->willReturn('USD');

        $priceFormatter = new PriceFormatter($configMock);

        $this->builder = new CaptureDataBuilder(
            $this->subjectReaderMock,
            $priceFormatter
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No Authorization Transaction to capture
     */
    public function testBuildWithException()
    {
        $amount = 10.00;
        $buildSubject = [
            'payment' => $this->paymentDataObject,
            'amount' => $amount
        ];

        $this->payment->expects($this->once())
            ->method('getCcTransId')
            ->willReturn('');

        $this->paymentDataObject->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->subjectReaderMock->expects($this->once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDataObject);

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $transactionId = 'ch_19RZmz2eZvKYlo2CktQObIT0';
        $amount = 10.00;

        $expected = [
            'transaction_id' => $transactionId,
            'amount' => '1000'
        ];

        $buildSubject = [
            'payment' => $this->paymentDataObject,
            'amount' => $amount
        ];

        $this->payment->expects($this->once())
            ->method('getCcTransId')
            ->willReturn($transactionId);
        $this->paymentDataObject->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->payment);
        $this->subjectReaderMock->expects($this->once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDataObject);
        $this->subjectReaderMock->expects($this->once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
