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

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\LocalizedException;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $additionalData = new DataObject($additionalData);

        $payment = $observer->getPaymentModel();
        if (!$payment instanceof InfoInterface) {
            $payment = $this->readMethodArgument($observer)->getInfoInstance();
        }

        if (!$payment instanceof InfoInterface) {
            throw new LocalizedException(__('Payment model does not provided.'));
        }

        $payment->setCcLast4($additionalData->getData('cc_last4'));
        $payment->setCcType($additionalData->getData('cc_type'));
        $payment->setCcExpMonth($additionalData->getData('cc_exp_month'));
        $payment->setCcExpYear($additionalData->getData('cc_exp_year'));

        if ($additionalData->getData('cc_token')) {
            $payment->setAdditionalInformation('cc_token', $additionalData->getData('cc_token'));
        }
        if ($additionalData->getData('cc_src')) {
            $payment->setAdditionalInformation('cc_src', $additionalData->getData('cc_src'));
        }
        if ($additionalData->getData('three_d_src')) {
            $payment->setAdditionalInformation('three_d_src', $additionalData->getData('three_d_src'));
        }
        if ($additionalData->getData('three_d_client_secret')) {
            $payment->setAdditionalInformation('three_d_client_secret', $additionalData->getData('three_d_client_secret'));
        }
    }
}
