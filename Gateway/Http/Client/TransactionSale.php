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

class TransactionSale extends AbstractTransaction
{
    /**
     * @param array $data
     * @return array|\Exception|\Stripe\ApiOperations\ApiResource|\Stripe\Error\Card
     */
    protected function process(array $data)
    {
        return $this->adapter->sale($data);
    }
}
