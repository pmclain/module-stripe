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
namespace Pmclain\Stripe\Unit\Integration\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Stripe\Stripe;
use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Pmclain\Stripe\Gateway\Config\Config;

class StripeAdapterTest extends TestCase
{
    const STRIPE_MOCK_URL = 'http://stripemock:12111';
    const STRIPE_MOCK_API_KEY = 'sk_test_123';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StripeAdapter
     */
    private $adapter;

    /**
     * @var Config|MockObject
     */
    private $configMock;
    
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        Stripe::$apiBase = self::STRIPE_MOCK_URL;

        $this->configMock = $this->createMock(Config::class);
        $this->configMock->method('getSecretKey')
            ->willReturn(self::STRIPE_MOCK_API_KEY);
        
        $this->adapter = $this->objectManager->create(
            StripeAdapter::class,
            [
                'config' => $this->configMock,
            ]
        );
    }

    public function testRefund()
    {
        $result = $this->adapter->refund('ch_1Bh7');

        $this->assertInstanceOf(\Stripe\Refund::class, $result);
    }

    public function testSale()
    {
        $attributes = [
            PaymentDataBuilder::CAPTURE => false,
            PaymentDataBuilder::AMOUNT => '1000',
            PaymentDataBuilder::CURRENCY => 'USD',
            PaymentDataBuilder::ORDER_ID => '100000001',
            PaymentDataBuilder::SOURCE => 'tok_visa',
        ];

        $result = $this->adapter->sale($attributes);

        $this->assertInstanceOf(\Stripe\Charge::class, $result);
    }

    public function testSaleSaveInVault()
    {
        $attributes = [
            PaymentDataBuilder::SAVE_IN_VAULT => true,
            PaymentDataBuilder::CAPTURE => false,
            PaymentDataBuilder::CUSTOMER => 'cus_C5IP3',
            PaymentDataBuilder::AMOUNT => '1000',
            PaymentDataBuilder::CURRENCY => 'USD',
            PaymentDataBuilder::ORDER_ID => '100000001',
            PaymentDataBuilder::SOURCE => 'tok_visa',
        ];

        $result = $this->adapter->sale($attributes);

        $this->assertInstanceOf(\Stripe\Charge::class, $result);
    }

    public function testSubmitForSettlement()
    {
        $result = $this->adapter->submitForSettlement('ch_1Bh7');

        $this->assertInstanceOf(\Stripe\Charge::class, $result);
    }
}
