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
namespace Pmclain\Stripe\Test\Unit\Block\Customer;

use Pmclain\Stripe\Block\Customer\CardRenderer;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Pmclain\Stripe\Model\Ui\ConfigProvider;
use Magento\Payment\Model\CcConfigProvider;

class CardRendererTest extends \PHPUnit_Framework_TestCase
{
  /** @var  PaymentTokenInterface|MockObject */
  private $paymentTokenMock;

  /** @var  CcConfigProvider|MockObject */
  private $ccConfigProviderMock;

  /** @var array */
  private $token = [
    'type' => 'VI',
    'maskedCC' => '4242',
    'expirationDate' => '2/2018'
  ];

  /** @var array */
  private $icons = [
    'VI' => [
      'url' => 'http://url',
      'width' => '60',
      'height' => '80'
    ]
  ];

  /** @var CardRenderer */
  private $cardRenderer;

  protected function setUp() {
    $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

    $this->paymentTokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
      ->getMockForAbstractClass();

    $this->ccConfigProviderMock = $this->getMockBuilder(CcConfigProvider::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->cardRenderer = $objectManager->getObject(
      CardRenderer::class,
      [
        'tokenDetails' => $this->token,
        'iconsProvider' => $this->ccConfigProviderMock
      ]
    );
  }

  public function testCanRender() {
    $this->paymentTokenMock->expects($this->once())
      ->method('getPaymentMethodCode')
      ->willReturn(ConfigProvider::CODE);

    $this->assertEquals(
      $this->cardRenderer->canRender($this->paymentTokenMock),
      true
    );
  }

  public function testGetNumberLast4Digits() {
    $this->assertEquals(
      $this->token['maskedCC'],
      $this->cardRenderer->getNumberLast4Digits()
    );
  }

  public function testGetExpDate() {
    $this->assertEquals(
      $this->token['expirationDate'],
      $this->cardRenderer->getExpDate()
    );
  }

  public function testGetIconUrl() {
    $this->ccConfigProviderMock->expects($this->any())
      ->method('getIcons')
      ->with()
      ->willReturn($this->icons);

    $this->assertEquals(
      $this->icons['VI']['url'],
      $this->cardRenderer->getIconUrl()
    );
  }

  public function testGetIconHeight() {
    $this->ccConfigProviderMock->expects($this->any())
      ->method('getIcons')
      ->with()
      ->willReturn($this->icons);

    $this->assertEquals(
      $this->icons['VI']['height'],
      $this->cardRenderer->getIconHeight()
    );
  }

  public function testGetIconWidth() {
    $this->ccConfigProviderMock->expects($this->any())
      ->method('getIcons')
      ->with()
      ->willReturn($this->icons);

    $this->assertEquals(
      $this->icons['VI']['width'],
      $this->cardRenderer->getIconWidth()
    );
  }
}