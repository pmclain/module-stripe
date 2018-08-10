<?php

namespace Pmclain\Stripe\Test\Unit\Gateway\Helper;

use PHPUnit\Framework\TestCase;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;
use Pmclain\Stripe\Gateway\Config\Config;

class PriceFormatterTest extends TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);

        $this->priceFormatter = new PriceFormatter($this->configMock);
    }

    /**
     * @dataProvider formatPriceDataProvider
     */
    public function testFormatPrice($expected, $price, $precision)
    {
        $this->configMock->method('getCurrencyPrecision')->willReturn($precision);

        $this->assertEquals($expected, $this->priceFormatter->formatPrice($price));
    }

    public function formatPriceDataProvider()
    {
        return [
            [
                '123', '123', '0',
            ],
            [
                '12300', '123', '2',
            ]
        ];
    }
}
