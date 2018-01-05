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
namespace Pmclain\Stripe\Test\Unit\Helper\Payment;

use Pmclain\Stripe\Helper\Payment\Formatter;

class FormatterTest extends \PHPUnit\Framework\TestCase
{
  use Formatter;

  /**
   * @param $subject int|float
   * @param $expectedResult int
   * @dataProvider testFormatPriceDataProvider
   */
  public function testFormatPrice($subject, $expectedResult) {
    $this->assertEquals($this->formatPrice($subject), $expectedResult);
  }

  public function testFormatPriceDataProvider() {
    return [
      [1, 100],
      [.1, 10],
      [25.73, 2573],
      [10, 1000],
    ];
  }
}