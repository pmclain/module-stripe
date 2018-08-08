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

namespace Pmclain\Stripe\Test\Unit\Block;

use Pmclain\Stripe\Block\Form;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Payment\Helper\Data;
use Pmclain\Stripe\Gateway\Config\Config as GatewayConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Payment\Model\MethodInterface;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /** @var  GatewayConfig|MockObject */
    private $gatewayConfigMock;

    /** @var  Data|MockObject */
    private $helperMock;

    /** @var  StoreManagerInterface|MockObject */
    private $storeManagerMock;

    /** @var  StoreInterface|MockObject */
    private $storeMock;

    /** @var  MethodInterface|MockObject */
    private $paymentMethodMock;

    /** @var  Form */
    private $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->gatewayConfigMock = $this->getMockBuilder(GatewayConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->paymentMethodMock = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();

        $this->block = $objectManager->getObject(
            Form::class,
            [
                'gatewayConfig' => $this->gatewayConfigMock,
                'paymentDataHelper' => $this->helperMock,
                '_storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * @param $config boolean The configuration value
     * @param $expectedResult boolean Expected result based on configuration
     *
     * @dataProvider providerTestUseCcv
     **/
    public function testUseCcv($config, $expectedResult)
    {
        $this->gatewayConfigMock->expects($this->once())
            ->method('isCcvEnabled')
            ->will($this->returnValue($config));

        $this->assertEquals($this->block->useCcv(), $expectedResult);
    }

    public function providerTestUseCcv()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testIsVaultEnabled()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->helperMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodMock);

        $this->paymentMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);

        $this->block->isVaultEnabled();
    }
}
