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

use Pmclain\Stripe\Block\Payment;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\Stripe\Model\Ui\ConfigProvider;

class PaymentTest extends \PHPUnit\Framework\TestCase
{
    /** @var  ConfigProvider|MockObject */
    private $configProviderMock;

    /** @var Payment */
    private $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configProviderMock = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $objectManager->getObject(
            Payment::class,
            [
                'config' => $this->configProviderMock
            ]
        );
    }

    public function testGetPaymentConfig()
    {
        $this->configProviderMock->expects($this->once())
            ->method('getConfig');

        $this->block->getPaymentConfig();
    }

    public function testGetCode()
    {
        $this->assertEquals(
            ConfigProvider::CODE,
            $this->block->getCode()
        );
    }
}
