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
namespace Pmclain\Stripe\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;

class AddressDataBuilder implements BuilderInterface
{
  const SHIPPING_ADDRESS = 'shipping';
  const STREET_ADDRESS = 'line1';
  const EXTENDED_ADDRESS = 'line2';
  const LOCALITY = 'city';
  const REGION = 'state';
  const POSTAL_CODE = 'postal_code';
  const COUNTRY_CODE = 'country';
  const NAME = 'name';
  const PHONE = 'phone';

  private $subjectReader;

  public function __construct(
    SubjectReader $subjectReader
  ) {
    $this->subjectReader = $subjectReader;
  }

  public function build(array $buildSubject) {
    $paymentDataObject = $this->subjectReader->readPayment($buildSubject);

    $order = $paymentDataObject->getOrder();
    $result = [];

    $shippingAddress = $order->getShippingAddress();
    if ($shippingAddress) {
      $result[self::SHIPPING_ADDRESS] = [
        'address' => [
          self::STREET_ADDRESS => $shippingAddress->getStreetLine1(),
          self::EXTENDED_ADDRESS => $shippingAddress->getStreetLine2(),
          self::LOCALITY => $shippingAddress->getCity(),
          self::REGION => $shippingAddress->getRegionCode(),
          self::POSTAL_CODE => $shippingAddress->getPostcode(),
          self::COUNTRY_CODE => $shippingAddress->getCountryId()
        ],
        self::NAME => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
        self::PHONE => $shippingAddress->getTelephone()
      ];
    }

    return $result;
  }
}