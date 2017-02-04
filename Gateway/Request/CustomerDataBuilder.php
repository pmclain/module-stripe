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
use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Stripe\Customer;

class CustomerDataBuilder implements BuilderInterface
{
  const CUSTOMER = 'customer';
  const FIRST_NAME = 'firstName';
  const LAST_NAME = 'lastName';
  const COMPANY = 'company';
  const EMAIL = 'email';
  const PHONE = 'phone';

  private $subjectReader;
  private $adapter;

  public function __construct(
    SubjectReader $subjectReader
  ) {
    $this->subjectReader = $subjectReader;
  }

  public function build(array $subject) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $order = $paymentDataObject->getOrder();
    $billingAddress = $order->getBillingAddress();

    return [
      self::CUSTOMER => [
        self::FIRST_NAME => $billingAddress->getFirstName(),
        self::LAST_NAME => $billingAddress->getLastName(),
        self::COMPANY => $billingAddress->getCompany(),
        self::PHONE => $billingAddress->getTelephone(),
        self::EMAIL => $billingAddress->getEmail()
      ]
    ];
  }
}