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

class OrderPlace
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        CartManagementInterface $cartManagement
    ) {
        $this->cartManagement = $cartManagement;
    }

    /**
     * @param Quote $quote
     * @param string $src
     * @param string $clientSecret
     * @throws LocalizedException
     * @throws \InvalidArgumentException
     */
    public function execute(Quote $quote, $src, $clientSecret)
    {
        $this->validatePaymentInformation($quote->getPayment(), $src, $clientSecret);

        $quote->collectTotals();
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $src
     * @param string $clientSecret
     * @throws \InvalidArgumentException
     */
    private function validatePaymentInformation($payment, $src, $clientSecret)
    {
        if ($payment->getAdditionalInformation('three_d_client_secret') !== $clientSecret
            || $payment->getAdditionalInformation('three_d_src') !== $src
        ) {
            throw new \InvalidArgumentException(
                __('Your payment information could not be validated. Please try again.')
            );
        }
    }
}
