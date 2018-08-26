<?php

namespace Pmclain\Stripe\Api;

interface StripeCustomerManagementInterface
{
    /**
     * @param int|string $magentoCustomerId
     * @return string
     */
    public function getStripeCustomerId($magentoCustomerId);

    /**
     * @param string $email
     * @return \Stripe\Customer
     */
    public function createStripeCustomer($email);

    /**
     * @param string $stripeCustomerId
     * @param string $source
     * @return string
     */
    public function addCustomerCard($stripeCustomerId, $source);
}
