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

namespace Pmclain\Stripe\Gateway\Request;

use Pmclain\Stripe\Api\StripeCustomerManagementInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\PriceFormatter;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Session\SessionManager;

class VaultPaymentDataBuilder implements BuilderInterface
{
    const AMOUNT = 'amount';
    const SOURCE = 'source';
    const ORDER_ID = 'description';
    const CURRENCY = 'currency';
    const CAPTURE = 'capture';
    const CUSTOMER = 'customer';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var SessionManager
     */
    private $session;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var StripeCustomerManagementInterface
     */
    private $stripeCustomerManagement;

    /**
     * PaymentDataBuilder constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param SessionManager $session
     * @param PriceFormatter $priceFormatter
     * @param StripeCustomerManagementInterface $stripeCustomerManagement
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        SessionManager $session,
        PriceFormatter $priceFormatter,
        StripeCustomerManagementInterface $stripeCustomerManagement
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->session = $session;
        $this->priceFormatter = $priceFormatter;
        $this->stripeCustomerManagement = $stripeCustomerManagement;
    }

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

        $result = [
            self::AMOUNT => $this->priceFormatter->formatPrice($this->subjectReader->readAmount($subject)),
            self::ORDER_ID => $order->getOrderIncrementId(),
            self::CURRENCY => $this->config->getCurrency(),
            self::SOURCE => $paymentToken->getGatewayToken(),
            self::CAPTURE => 'false',
            self::CUSTOMER => $this->stripeCustomerManagement->getStripeCustomerId($this->session->getCustomerId()),
        ];

        return $result;
    }
}
