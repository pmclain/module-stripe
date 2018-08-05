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

use Pmclain\Stripe\Gateway\Response\RefundHandler;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;

class RefundHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldCloseParentTransaction()
    {
        $subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCreditmemo'])
            ->getMock();
        $creditmemo = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInvoice'])
            ->getMock();
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['canRefund'])
            ->getMock();

        $invoice->expects($this->once())
            ->method('canRefund')
            ->willReturn(true);
        $creditmemo->expects($this->once())
            ->method('getInvoice')
            ->willReturn($invoice);
        $payment->expects($this->once())
            ->method('getCreditmemo')
            ->willReturn($creditmemo);

        $handler = new RefundHandler($subjectReader);
        $reflection = new \ReflectionClass(get_class($handler));
        $method = $reflection->getMethod('shouldCloseParentTransaction');
        $method->setAccessible(true);

        $this->assertFalse($method->invokeArgs($handler, [$payment]));
    }
}
