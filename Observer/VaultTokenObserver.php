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

namespace Pmclain\Stripe\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use Stripe\Stripe;
use Stripe\Customer;

class VaultTokenObserver implements ObserverInterface
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Config */
    private $config;

    /**
     * VaultTokenObserver constructor.
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Config $config,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->initCredentials();
    }

    protected function initCredentials()
    {
        Stripe::setApiKey($this->config->getSecretKey());
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $token = $observer->getObject();
        if ($token->getIsActive()) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($token->getCustomerId());
        } catch (\Exception $e) {
            return;
        }

        $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');
        if (!$stripeCustomerId) {
            return;
        }

        try {
            $stripeCustomer = Customer::retrieve($stripeCustomerId->getValue());
            $stripeCustomer->sources->retrieve($token->getGatewayToken())->delete();
        } catch (\Exception $e) {
            return;
        }
    }
}
