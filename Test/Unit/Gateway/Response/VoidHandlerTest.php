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

namespace Pmclain\Stripe\Test\Unit\Gateway\Response;

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Response\VoidHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class VoidHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandle()
    {
        $transactionId = 'ch_19Rjix2eZvKYlo2C5VFbcuXf';
        $paymentDataObject = $this->createMock(PaymentDataObjectInterface::class);
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subject = ['payment' => $paymentDataObject];
        $response = ['object' => ['id' => $transactionId]];

        $subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subjectReader->expects($this->once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentDataObject);
        $paymentDataObject->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($payment);
        $subjectReader->expects($this->once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($response['object']);
        $payment->expects($this->never())
            ->method('setTransactionId');
        $payment->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true);
        $payment->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $handler = new VoidHandler($subjectReader);
        $handler->handle($subject, $response);
    }
}
