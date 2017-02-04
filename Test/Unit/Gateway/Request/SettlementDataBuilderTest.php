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
 */namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use Pmclain\Stripe\Gateway\Request\SettlementDataBuilder;

class SettlementDataBuilderTest extends \PHPUnit_Framework_TestCase
{
  public function testBuild() {
    $builder = new SettlementDataBuilder();
    $this->assertEquals(
      [SettlementDataBuilder::SUBMIT_FOR_SETTLEMENT => true],
      $builder->build([])
    );
  }
}