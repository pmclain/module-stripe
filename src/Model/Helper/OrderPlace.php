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

namespace Pmclain\Stripe\Model\Helper;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Group;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Session;
use Magento\Checkout\Helper\Data;

class OrderPlace
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $checkoutHelper;

    /**
     * OrderPlace constructor.
     * @param CartManagementInterface $cartManagement
     * @param Session $session
     * @param Data $helper
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        Session $session,
        Data $helper
    ) {
        $this->cartManagement = $cartManagement;
        $this->customerSession = $session;
        $this->checkoutHelper = $helper;
    }

    /**
     * @param Quote $quote
     * @param string $src
     * @param string $clientSecret
     * @throws LocalizedException
     */
    public function execute(Quote $quote, $src, $clientSecret)
    {
        $this->validatePaymentInformation($quote->getPayment(), $src, $clientSecret);

        if ($this->getCheckoutMethod($quote) === Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote($quote);
        }

        $quote->collectTotals();
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $src
     * @param string $clientSecret
     * @throws LocalizedException
     */
    private function validatePaymentInformation($payment, $src, $clientSecret)
    {
        if ($payment->getAdditionalInformation('three_d_client_secret') !== $clientSecret
            || $payment->getAdditionalInformation('three_d_src') !== $src
        ) {
            throw new LocalizedException(
                __('Your payment information could not be validated. Please try again.')
            );
        }
    }

    /**
     * Get checkout method
     *
     * @param Quote $quote
     * @return string
     */
    private function getCheckoutMethod(Quote $quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }

        return $quote->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param Quote $quote
     * @return void
     */
    private function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);
    }
}
