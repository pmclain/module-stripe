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

use Magento\Payment\Model\InfoInterface;
use Pmclain\Stripe\Api\StripeCustomerManagementInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pmclain\Stripe\Model\Adapter\StripeAdapter;

class ThreeDSecureBuilder implements BuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var StripeCustomerManagementInterface
     */
    private $stripeCustomerManagement;

    /**
     * @var StripeAdapter
     */
    private $stripeAdapter;

    /**
     * ThreeDSecureBuilder constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param StripeCustomerManagementInterface $stripeCustomerManagement
     * @param StripeAdapter $stripeAdapter
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        StripeCustomerManagementInterface $stripeCustomerManagement,
        StripeAdapter $stripeAdapter
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->stripeCustomerManagement = $stripeCustomerManagement;
        $this->stripeAdapter = $stripeAdapter;
    }

    /**
     * @param array $subject
     * @return array
     * @throws \Magento\Framework\Validator\Exception
     */
    public function build(array $subject)
    {
        $result = [];
        if (!$this->config->isRequireThreeDSecure()
            || (float)$this->subjectReader->readAmount($subject) < $this->config->getThreeDSecureThreshold()
        ) {
            return $result;
        }

        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();

        $source = $this->getSourceForCharge($payment);
        if ($source) {
            $result[PaymentDataBuilder::SOURCE] = $source;
        }

        return $result;
    }

    /**
     * @param InfoInterface $payment
     * @return string|false
     */
    private function getSourceForCharge($payment)
    {
        $threeDSource = $this->stripeAdapter->retrieveSource($payment->getAdditionalInformation('three_d_src'));
        if ($threeDSource->status === 'failed') {
            return $payment->getAdditionalInformation('cc_src') ?: false;
        }

        return $payment->getAdditionalInformation('three_d_src');
    }
}
