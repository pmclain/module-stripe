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

namespace Pmclain\Stripe\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Pmclain\Stripe\Model\InstantPurchase\CreditCard\TokenFormatter;

class Info extends ConfigurableInfo
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Info constructor.
     * @param Context $context
     * @param ConfigInterface $config
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        PaymentTokenManagementInterface $paymentTokenManagement,
        SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->serializer = $serializer;
    }

    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * @param null $transport
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();

        $this->setDataToTransfer(
            $transport,
            'Card Type',
            $this->getCcType($payment)
        );
        $this->setDataToTransfer(
            $transport,
            'Card Last 4 Digits',
            $this->getCcLast4($payment)
        );

        return $transport;
    }

    /**
     * @param InfoInterface $payment
     * @return string
     */
    private function getCcLast4(InfoInterface $payment)
    {
        if ($payment->getCcLast4()) {
            return $payment->getCcLast4();
        }

        $token = $this->getPaymentToken($payment);
        $details = $this->serializer->unserialize($token->getTokenDetails());

        $last4 = '';
        if (!empty($details['maskedCC'])) {
            $last4 = $details['maskedCC'];
        }

        return $last4;
    }

    /**
     * @param InfoInterface $payment
     * @return string
     */
    private function getCcType(InfoInterface $payment)
    {
        if ($payment->getCcType()) {
            return $payment->getCcType();
        }

        $token = $this->getPaymentToken($payment);
        $details = $this->serializer->unserialize($token->getTokenDetails());

        $type = '';
        if (!empty($details['type'])) {
            $type = $details['type'];
        }

        if (!empty(TokenFormatter::$baseCardTypes[$type])) {
            $type = TokenFormatter::$baseCardTypes[$type];
        }

        return $type;
    }

    /**
     * @param InfoInterface $payment
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface|null
     */
    private function getPaymentToken(InfoInterface $payment)
    {
        return $this->paymentTokenManagement->getByPublicHash(
            $payment->getAdditionalInformation('public_hash'),
            $payment->getAdditionalInformation('customer_id')
        );
    }
}
