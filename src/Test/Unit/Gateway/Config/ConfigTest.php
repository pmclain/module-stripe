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

namespace Pmclain\Stripe\Test\Unit\Gateway\Config;

use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    const METHOD_CODE = 'pmclain_stripe';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->model = new Config($this->scopeConfigMock, self::METHOD_CODE);
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider getAvailableCardTypesProvider
     */
    public function testGetAvailableCardTypes($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                $this->getPath(Config::KEY_CC_TYPES),
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->getAvailableCardTypes());
    }

    public function getAvailableCardTypesProvider()
    {
        return [
            ['VI,MC,AE,DI,JCB,DN', ['VI', 'MC', 'AE', 'DI', 'JCB', 'DN']],
            ['', []]
        ];
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider getCcTypesMapperDataProvider
     */
    public function testGetCcTypesMapper($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                $this->getPath(Config::KEY_CC_TYPES_STRIPE_MAPPER),
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->getCcTypesMapper());
    }

    public function getCcTypesMapperDataProvider()
    {
        return [
            [
                '{"visa":"VI","american-express":"AE"}',
                ['visa' => 'VI', 'american-express' => 'AE']
            ],
            [
                '{invalid json}',
                []
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider yesNoDataProvider
     */
    public function testIsCcvEnabled($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                $this->getPath(Config::KEY_USE_CCV),
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->isCcvEnabled());
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider yesNoDataProvider
     */
    public function testGetEnvironment($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                $this->getPath(Config::KEY_ENVIRONMENT),
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->getEnvironment());
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider yesNoDataProvider
     */
    public function testIsActive($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                $this->getPath(Config::KEY_ACTIVE),
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->isActive());
    }

    public function testGetPublishableKeyTestMode()
    {
        $result = 'pub_key';
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [
                    $this->getPath(Config::KEY_ENVIRONMENT),
                    ScopeInterface::SCOPE_STORE,
                    null
                ],
                [
                    $this->getPath(Config::KEY_TEST_PUBLISHABLE_KEY),
                    ScopeInterface::SCOPE_STORE,
                    null
                ]
            )->willReturnOnConsecutiveCalls(true, $result);
        $this->assertEquals($result, $this->model->getPublishableKey());
    }

    public function testGetPublishableKeyLiveMode()
    {
        $result = 'pub_key';
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [
                    $this->getPath(Config::KEY_ENVIRONMENT),
                    ScopeInterface::SCOPE_STORE,
                    null
                ],
                [
                    $this->getPath(Config::KEY_LIVE_PUBLISHABLE_KEY),
                    ScopeInterface::SCOPE_STORE,
                    null
                ]
            )->willReturnOnConsecutiveCalls(false, $result);
        $this->assertEquals($result, $this->model->getPublishableKey());
    }

    public function testGetSecretKeyTestMode()
    {
        $result = 'sec_key';
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [
                    $this->getPath(Config::KEY_ENVIRONMENT),
                    ScopeInterface::SCOPE_STORE,
                    null
                ],
                [
                    $this->getPath(Config::KEY_TEST_SECRET_KEY),
                    ScopeInterface::SCOPE_STORE,
                    null
                ]
            )->willReturnOnConsecutiveCalls(true, $result);
        $this->assertEquals($result, $this->model->getSecretKey());
    }

    public function testGetSecretKeyLiveMode()
    {
        $result = 'sec_key';
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [
                    $this->getPath(Config::KEY_ENVIRONMENT),
                    ScopeInterface::SCOPE_STORE,
                    null
                ],
                [
                    $this->getPath(Config::KEY_LIVE_SECRET_KEY),
                    ScopeInterface::SCOPE_STORE,
                    null
                ]
            )->willReturnOnConsecutiveCalls(false, $result);
        $this->assertEquals($result, $this->model->getSecretKey());
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider yesNoDataProvider
     */
    public function testIsTestMode($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                $this->getPath(Config::KEY_ENVIRONMENT),
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->isTestMode());
    }

    public function yesNoDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }
}
