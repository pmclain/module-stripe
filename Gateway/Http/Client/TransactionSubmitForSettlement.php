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

use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Gateway\Request\CaptureDataBuilder;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;

class TransactionSubmitForSettlement extends AbstractTransaction
{
    /**
     * @param array $data
     * @return mixed
     */
    protected function process(array $data)
    {
        return $this->adapter->submitForSettlement(
            $data[CaptureDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
