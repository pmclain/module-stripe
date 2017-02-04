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
 */namespace Pmclain\Stripe\Test\Unit\Gateway\Http\Client;

use Pmclain\Stripe\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var TransferFactory
   */
  private $transferFactory;

  /**
   * @var TransferFactory
   */
  private $transferMock;

  /**
   * @var TransferBuilder|\PHPUnit_Framework_MockObject_MockObject
   */
  private $transferBuilder;

  protected function setUp() {
    $this->transferBuilder = $this->getMock(TransferBuilder::class);
    $this->transferMock = $this->getMock(TransferInterface::class);

    $this->transferFactory = new TransferFactory($this->transferBuilder);
  }

  public function testCreate() {
    $request = ['data1', 'data1'];

    $this->transferBuilder->expects($this->once())
      ->method('setBody')
      ->with($request)
      ->willReturnSelf();

    $this->transferBuilder->expects($this->once())
      ->method('build')
      ->willReturn($this->transferMock);

    $this->assertEquals($this->transferMock, $this->transferFactory->create($request));
  }
}