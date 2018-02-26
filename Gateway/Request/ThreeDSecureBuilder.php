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
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Stripe\Stripe;
use Stripe\Source;

class ThreeDSecureBuilder implements BuilderInterface
{
    const SOURCE = 'source';
    const SOURCE_FOR_VAULT = 'source_for_vault';

    /** @var Config */
    protected $config;

    /** @var SubjectReader */
    protected $subjectReader;

    /**
     * PaymentDataBuilder constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
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

        Stripe::setApiKey($this->config->getSecretKey());

        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();

        $source = $this->getSourceForCharge($payment);
        if ($source) {
            $result[self::SOURCE] = $source;
            $result[self::SOURCE_FOR_VAULT] = $payment->getAdditionalInformation('cc_src');
        }

        return $result;
    }

    /**
     * @param InfoInterface $payment
     * @return string|false
     */
    private function getSourceForCharge($payment)
    {
        /** @var Source $threeDSource */
        $threeDSource = Source::retrieve($payment->getAdditionalInformation('three_d_src'));
        if ($threeDSource->status === 'failed') {
            return $payment->getAdditionalInformation('cc_src') ?: false;
        }

        return $payment->getAdditionalInformation('three_d_src');
    }
}
