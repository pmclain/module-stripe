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

namespace Pmclain\Stripe\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SettlementDataBuilder implements BuilderInterface
{
    const SUBMIT_FOR_SETTLEMENT = 'capture';

    /**
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        return [self::SUBMIT_FOR_SETTLEMENT => true];
    }
}
