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

namespace Pmclain\Stripe\Gateway\Helper;

use Magento\Payment\Gateway\Helper;

class SubjectReader
{
    /**
     * @param array $subject
     * @return array
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);

        if (!is_object($response['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        if ($response['object'] instanceof \Stripe\Error\Card) {
            return [
                'error' => true,
                'message' => __($response['object']->getMessage())
            ];
        }

        return $response['object']->__toArray();
    }

    /**
     * @codeCoverageIgnore
     * @param array $subject
     * @return \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * @param array $subject
     * @return mixed
     */
    public function readTransaction(array $subject)
    {
        if (!is_object($subject['object'])) {
            throw new \InvalidArgumentException('Response object does not exist');
        }

        return $subject['object']->__toArray();
    }

    /**
     * @codeCoverageIgnore
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * @param array $subject
     * @return int
     */
    public function readCustomerId(array $subject)
    {
        if (!isset($subject['customer_id'])) {
            throw new \InvalidArgumentException('The customerId field does not exist');
        }

        return (int)$subject['customer_id'];
    }
}
