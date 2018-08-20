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

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\Stripe\Block\Info;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentTokenInterface|MockObject
     */
    private $paymentTokenMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentTokenManagementInterface|MockObject
     */
    private $paymentTokenManagementMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Info
     */
    private $block;

    protected function setUp()
    {
        $this->paymentTokenMock = $this->createMock(PaymentTokenInterface::class);
        $this->paymentTokenManagementMock = $this->createMock(PaymentTokenManagementInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->paymentMock = $this->createMock(Payment::class);

        $this->paymentTokenManagementMock->method('getByPublicHash')->willReturn($this->paymentTokenMock);
        $this->paymentTokenMock->method('getTokenDetails')->willReturn(json_encode([
            'type' => 'AE',
            'maskedCC' => '4444',
            'expirationDate' => '12/29'
        ]));

        $this->block = new Info(
            $this->contextMock,
            $this->configMock,
            $this->paymentTokenManagementMock,
            new Json()
        );

        $this->block->setData('info', $this->paymentMock);
    }

    public function testGetSpecificInformationFromToken()
    {
        $expected = [
            'Card Type' => 'American Express',
            'Card Last 4 Digits' => '4444',
        ];

        $this->assertEquals($expected, $this->block->getSpecificInformation());
    }

    public function testGetSpecificInformation()
    {
        $expected = [
            'Card Type' => 'American Express',
            'Card Last 4 Digits' => '4444',
        ];

        $this->paymentMock->method('getCcType')->willReturn('American Express');
        $this->paymentMock->method('getCcLast4')->willReturn('4444');

        $this->assertEquals($expected, $this->block->getSpecificInformation());
    }
}
