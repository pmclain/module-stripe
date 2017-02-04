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
namespace Pmclain\Stripe\Gateway\Http\Client;

use Magento\Sales\Model\Order\Payment;
use Pmclain\Stripe\Gateway\Request\CaptureDataBuilder;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;

class TransactionSubmitForSettlement extends AbstractTransaction
{
  protected function process(array $data) {
    return $this->adapter->submitForSettlement(
      $data[CaptureDataBuilder::TRANSACTION_ID],
      $data[PaymentDataBuilder::AMOUNT]
    );
  }
}