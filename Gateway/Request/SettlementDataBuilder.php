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
namespace Pmclain\Stripe\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SettlementDataBuilder implements BuilderInterface
{
  const SUBMIT_FOR_SETTLEMENT = 'capture';

  public function build(array $subject) {
    return [self::SUBMIT_FOR_SETTLEMENT => true];
  }
}