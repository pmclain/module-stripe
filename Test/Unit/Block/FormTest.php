<?php
/**
 * Pmclain_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category  Pmclain
 * @package   Pmclain_Stripe
 * @copyright Copyright (c) 2017
 * @license   https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */
namespace Pmclain\Stripe\Test\Unit\Block;

use Pmclain\Stripe\Block\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
  private $context;
  private $paymentConfig;
  private $gatewayConfig;

  private $block;

  protected function setUp() {
    $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
      ->disableOriginalConstructor()
      ->getMock();

    $this->paymentConfig = $this->getMockBuilder('Magento\Payment\Model\Config')
      ->disableOriginalConstructor()
      ->getMock();

    $this->gatewayConfig = $this->getMockBuilder('Pmclain\Stripe\Gateway\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();

    $this->block = new Form($this->context, $this->paymentConfig, $this->gatewayConfig);
  }

  /**
   * @param $config boolean The configuration value
   * @param $expectedResult boolean Expected result based on configuration
   *
   * @dataProvider providerTestUseCcv
   **/
  public function testUseCcv($config, $expectedResult) {
    $this->gatewayConfig->expects($this->once())
      ->method('isCcvEnabled')
      ->will($this->returnValue($config));

    $this->assertEquals($this->block->useCcv(), $expectedResult);
  }

  public function providerTestUseCcv() {
    return [
      [true, true],
      [false, false]
    ];
  }
}