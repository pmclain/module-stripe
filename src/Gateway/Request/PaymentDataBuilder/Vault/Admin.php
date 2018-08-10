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

namespace Pmclain\Stripe\Gateway\Request\PaymentDataBuilder\Vault;

use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder\Vault;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\Model\Session\Quote;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;

class Admin extends Vault
{
    /** @var Quote $adminSession */
    private $adminSession;

    /**
     * Admin constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param Quote $session
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Quote $session,
        PriceFormatter $priceFormatter
    ) {
        parent::__construct(
            $config,
            $subjectReader,
            $customerSession,
            $customerRepository,
            $priceFormatter
        );
        $this->adminSession = $session;
    }

    /**
     * @return \Magento\Framework\Api\AttributeInterface|mixed|null
     */
    protected function getStripeCustomerId()
    {
        $customer = $this->customerRepository->getById($this->adminSession->getCustomerId());
        $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');

        if (!$stripeCustomerId) {
            $stripeCustomerId = $this->createNewStripeCustomer($customer->getEmail());
            $customer->setCustomAttribute(
                'stripe_customer_id',
                $stripeCustomerId
            );

            $this->customerRepository->save($customer);

            return $stripeCustomerId;
        }

        return $stripeCustomerId->getValue();
    }
}
