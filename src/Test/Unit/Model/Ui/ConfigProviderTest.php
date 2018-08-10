<?php

namespace Pmclain\Stripe\Test\Unit\Model\Ui;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\UrlInterface;
use Pmclain\Stripe\Model\Ui\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ConfigProvider
     */
    private $model;

    protected function setUp()
    {
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->configMock = $this->createMock(ScopeConfigInterface::class);

        $this->model = new ConfigProvider(
            $this->configMock,
            $this->urlMock
        );
    }

    public function testGetConfig()
    {
        $config = $this->model->getConfig();
        $this->assertArrayHasKey(ConfigProvider::CODE, $config['payment']);
    }
}
