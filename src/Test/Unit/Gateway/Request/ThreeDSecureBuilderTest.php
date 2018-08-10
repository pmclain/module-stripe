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

use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Pmclain\Stripe\Gateway\Request\ThreeDSecureBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;

class ThreeDSecureBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThreeDSecureBuilder
     */
    private $builder;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(InfoInterface::class);
        $this->subjectReader = new SubjectReader();

        $this->paymentDataObjectMock->method('getPayment')->willReturn($this->paymentMock);

        $this->builder = new ThreeDSecureBuilder($this->configMock, $this->subjectReader);
    }

    public function testBuildDisabled()
    {
        $this->configMock->method('isRequireThreeDSecure')->willReturn(false);
        $this->assertEquals([], $this->builder->build([]));
    }

    public function testBuildBelowThreshold()
    {
        $this->configMock->method('isRequireThreeDSecure')->willReturn(true);
        $this->configMock->method('getThreeDSecureThreshold')->willReturn(2.00);

        $subject = [
            'amount' => 1.00,
        ];

        $this->assertEquals([], $this->builder->build($subject));
    }
}
