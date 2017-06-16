<?php
/**
 * Pmclain_Stripe extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPL v3 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl.txt
 *
 * @category  Pmclain
 * @package   Pmclain_Stripe
 * @copyright Copyright (c) 2017
 * @license   https://www.gnu.org/licenses/gpl.txt GPL v3 License
 */
namespace Pmclain\Stripe\Gateway\Helper;

use Magento\Payment\Gateway\Helper;

class SubjectReader
{
  /**
   * @param array $subject
   * @return array
   */
  public function readResponseObject(array $subject) {
    $response = Helper\SubjectReader::readResponse($subject);

    if(!is_object($response['object'])) {
      throw new \InvalidArgumentException('Response object does not exist');
    }

    if($response['object'] instanceof \Stripe\Error\Card) {
      return [
        'error' => true,
        'message' => __($response['object']->getMessage())
      ];
    }

    return $response['object']->__toArray();
  }

  public function readPayment(array $subject) {
    return Helper\SubjectReader::readPayment($subject);
  }

  public function readTransaction(array $subject) {
    if(!is_object($subject['object'])) {
      throw new \InvalidArgumentException('Response object does not exist');
    }

    return $subject['object']->__toArray();
  }

  public function readAmount(array $subject) {
    return Helper\SubjectReader::readAmount($subject);
  }

  public function readCustomerId(array $subject) {
    if(!isset($subject['customer_id'])) {
      throw new \InvalidArgumentException('The customerId field does not exist');
    }

    return (int) $subject['customer_id'];
  }
}