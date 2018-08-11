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

namespace Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;

use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder;

class Vault extends PaymentDataBuilder
{
    /**
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();
        $order = $paymentDataObject->getOrder();

        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();

        $stripeCustomerId = $this->getStripeCustomerId();

        $result = [
            self::AMOUNT => $this->priceFormatter->formatPrice($this->subjectReader->readAmount($subject)),
            self::ORDER_ID => $order->getOrderIncrementId(),
            self::CURRENCY => $this->config->getCurrency(),
            self::SOURCE => $paymentToken->getGatewayToken(),
            self::CAPTURE => 'false',
            self::CUSTOMER => $stripeCustomerId
        ];

        return $result;
    }
}
