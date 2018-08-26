<?php

namespace Pmclain\Stripe\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Pmclain\Stripe\Api\StripeCustomerManagementInterface;
use Pmclain\Stripe\Model\Adapter\StripeAdapter;

class StripeCustomerManagement implements StripeCustomerManagementInterface
{
    /**
     * @var StripeAdapter
     */
    private $adapter;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * StripeCustomerManagement constructor.
     * @param StripeAdapter $adapter
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        StripeAdapter $adapter,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->adapter = $adapter;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param int|string $magentoCustomerId
     * @return string
     */
    public function getStripeCustomerId($magentoCustomerId)
    {
        $customer = $this->customerRepository->getById($magentoCustomerId);
        $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');

        if (!$stripeCustomerId) {
            $stripeCustomer = $this->createStripeCustomer($customer->getEmail());
            $customer->setCustomAttribute(
                'stripe_customer_id',
                $stripeCustomer->id
            );

            $this->customerRepository->save($customer);

            return $stripeCustomer->id;
        }

        return $stripeCustomerId->getValue();
    }

    /**
     * @param string $email
     * @return \Stripe\Customer
     */
    public function createStripeCustomer($email)
    {
        return $result = $this->adapter->createCustomer([
            'description' => 'Customer for ' . $email,
        ]);
    }

    /**
     * @param string $stripeCustomerId
     * @param string $source
     * @return string
     */
    public function addCustomerCard($stripeCustomerId, $source)
    {
        $customer = $this->adapter->retrieveCustomer($stripeCustomerId);
        /** @var \Stripe\Card $card */
        $card = $customer->sources->create(['source' => $source]);

        return $card->id;
    }
}
