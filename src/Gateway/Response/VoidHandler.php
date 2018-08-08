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

namespace Pmclain\Stripe\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Gateway\Response\TransactionIdHandler;

class VoidHandler extends TransactionIdHandler
{
    /**
     * @param Payment $orderPayment
     * @param $transaction
     */
    protected function setTransactionId(Payment $orderPayment, $transaction)
    {
        return;
    }

    /**
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return true;
    }

    /**
     * @param Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}
