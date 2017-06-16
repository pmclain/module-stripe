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

use Pmclain\Stripe\Block\Info;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;

class InfoTest extends \PHPUnit_Framework_TestCase
{
  public function testGetLabel() {
    $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

    $context = $this->getMockBuilder(Context::class)
      ->disableOriginalConstructor()
      ->getMock();
    $config = $this->getMockBuilder(ConfigInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $block = $objectManager->getObject(Info::class);

    $reflection = new \ReflectionClass(get_class($block));
    $method = $reflection->getMethod('getLabel');
    $method->setAccessible(true);

    $this->assertEquals(
      $method->invokeArgs($block, ['testing']),
      'testing'
    );
  }
}