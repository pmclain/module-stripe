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

use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Observer\DataAssignObserver;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Pmclain\Stripe\Helper\Payment\Formatter;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Stripe\Customer;

class VaultPaymentDataBuilder implements BuilderInterface
{
  use Formatter;
  
  const AMOUNT = 'amount';
  const SOURCE = 'source';
  const ORDER_ID = 'description';
  const CURRENCY = 'currency';
  const CAPTURE = 'capture';
  const CUSTOMER = 'customer';
  
  private $config;
  private $subjectReader;
  private $customerSession;
  private $customerRepository;
  
  public function __construct(
    Config $config,
    SubjectReader $subjectReader,
    Session $customerSession,
    CustomerRepositoryInterface $customerRepository
  ) {
    $this->config = $config;
    $this->subjectReader = $subjectReader;
    $this->customerSession = $customerSession;
    $this->customerRepository = $customerRepository;
  }
  
  public function build(array $subject) {
    $paymentDataObject = $this->subjectReader->readPayment($subject);
    $payment = $paymentDataObject->getPayment();
    $order = $paymentDataObject->getOrder();

    $extensionAttributes = $payment->getExtensionAttributes();
    $paymentToken = $extensionAttributes->getVaultPaymentToken();

    $stripeCustomerId = $this->getStripeCustomerId();
    
    $result = [
      self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($subject)),
      self::ORDER_ID => $order->getOrderIncrementId(),
      self::CURRENCY => $this->config->getCurrency(),
      self::SOURCE => $paymentToken->getGatewayToken(),
      self::CAPTURE => 'false',
      self::CUSTOMER => $stripeCustomerId->getValue()
    ];

    return $result;
  }

  protected function getStripeCustomerId() {
    $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
    $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');

    if(!$stripeCustomerId) {
      $stripeCustomerId = $this->createNewStripeCustomer($customer->getEmail());
      $customer->setCustomAttribute('stripe_customer_id', $stripeCustomerId);

      $this->customerRepository->save($customer);
    }

    return $stripeCustomerId;
  }

  protected function createNewStripeCustomer($email) {
    $result = Customer::create([
      'description' => 'Customer for ' . $email,
    ]);

    return $result->id;
  }
}