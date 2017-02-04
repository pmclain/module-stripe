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
 */namespace Pmclain\Stripe\Test\Unit\Helper;

use Pmclain\Stripe\Model\Adminhtml\Source\Cctype as CcTypeSource;
use Pmclain\Stripe\Helper\CcType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CcTypeTest extends \PHPUnit_Framework_TestCase
{
  /** @var ObjectManager */
  private $objectManager;

  /** @var CcType */
  private $helper;

  /** @var CcTypeSource|\PHPUnit_Framework_MockObject_MockObject */
  private $ccTypeSource;
  
  protected function setUp() {
    $this->objectManager = new ObjectManager($this);

    $this->ccTypeSource = $this->getMockBuilder(CcTypeSource::class)
      ->disableOriginalConstructor()
      ->setMethods(['toOptionArray'])
      ->getMock();

    $this->helper = $this->objectManager->getObject(
      CcType::class,
      ['ccTypeSource' => $this->ccTypeSource]
    );
  }

  public function testGetCcTypes() {
    $this->ccTypeSource->expects($this->once())
      ->method('toOptionArray')
      ->willReturn([
        'label' => 'Visa', 'value' => 'VI'
      ]);
    $this->helper->getCcTypes();
    $this->ccTypeSource->expects($this->never())
      ->method('toOptionArray');
    $this->helper->getCcTypes();
  }
}
