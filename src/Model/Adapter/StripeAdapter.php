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

namespace Pmclain\Stripe\Model\Adapter;

use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Refund;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;
use Pmclain\Stripe\Gateway\Request\ThreeDSecureBuilder;

class StripeAdapter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * StripeAdapter constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
        $this->initCredentials();
    }

    protected function initCredentials()
    {
        Stripe::setApiKey($this->config->getSecretKey());
    }

    /**
     * @param $transactionId
     * @param null $amount
     * @return \Stripe\ApiResource
     */
    public function refund($transactionId, $amount = null)
    {
        return Refund::create([
            'charge' => $transactionId,
            'amount' => $amount,
        ]);
    }

    /**
     * @param $attributes
     * @return array|\Exception|\Stripe\ApiResource|\Stripe\Error\Card
     */
    public function sale($attributes)
    {
        if (isset($attributes[PaymentDataBuilder::SAVE_IN_VAULT])) {
            unset($attributes[PaymentDataBuilder::SAVE_IN_VAULT]);
            $attributes = $this->saveCustomerCard($attributes);

            if ($attributes instanceof \Stripe\Error\Card) {
                return $attributes;
            }
        } elseif (isset($attributes[ThreeDSecureBuilder::SOURCE_FOR_VAULT])) {
            unset($attributes[ThreeDSecureBuilder::SOURCE_FOR_VAULT]);
        }

        try {
            return Charge::create($attributes);
        } catch (\Stripe\Error\Card $e) {
            return $e;
        }
    }

    /**
     * @param $transactionId
     * @param null $amount
     * @return mixed
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        $charge = Charge::retrieve($transactionId);
        return $charge->capture(['amount' => $amount]);
    }

    /**
     * @param $transactionId
     * @return \Stripe\ApiResource
     */
    public function void($transactionId)
    {
        return Refund::create(['charge' => $transactionId]);
    }

    /**
     * @param $attributes
     * @return \Exception|\Stripe\Error\Card|array
     * @throws \Magento\Framework\Validator\Exception
     */
    protected function saveCustomerCard($attributes)
    {
        try {
            $stripeCustomer = Customer::retrieve($attributes[PaymentDataBuilder::CUSTOMER]);

            if (isset($attributes[ThreeDSecureBuilder::SOURCE_FOR_VAULT])) {
                $stripeCustomer->sources->create([
                    'source' => $attributes[ThreeDSecureBuilder::SOURCE_FOR_VAULT]
                ]);

                unset($attributes[ThreeDSecureBuilder::SOURCE_FOR_VAULT]);
            } else {
                $card = $stripeCustomer->sources->create([
                    'source' => $attributes[PaymentDataBuilder::SOURCE]
                ]);

                $attributes[PaymentDataBuilder::SOURCE] = $card->id;
            }

            return $attributes;
        } catch (\Stripe\Error\Card $e) {
            return $e;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }
    }
}
