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

namespace Pmclain\Stripe\Gateway\Http\Client;

use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;

class TransactionRefund extends AbstractTransaction
{
    /**
     * @param array $data
     * @return \Stripe\ApiOperations\ApiResource
     */
    protected function process(array $data)
    {
        return $this->adapter->refund(
            $data['transaction_id'],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
