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

use Pmclain\Stripe\Gateway\Request\VoidDataBuilder;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class VoidDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VoidDataBuilder
     */
    private $builder;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VoidDataBuilder($this->subjectReader);
    }

    /**
     *
     * @dataProvider testBuildDataProvider
     */
    public function testBuild($parentTransId, $lastTransId)
    {
        $paymentDataObject = $this->createMock(PaymentDataObjectInterface::class);
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
        if (!$parentTransId) {
            $paymentMock->expects($this->once())
                ->method('getLastTransId')
                ->willReturn($lastTransId);
        }

        $this->assertEquals(
            ['transaction_id' => $parentTransId ?: $lastTransId],
            $this->builder->build($buildSubject)
        );
    }

    public function testBuildDataProvider()
    {
        return [
            ['ch_19RZmz2eZvKYlo2CktQObIT0', null],
            [false, 'ch_19RZmz2eZvKYlo2CktQObIT0']
        ];
    }
}
