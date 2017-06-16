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
namespace Pmclain\Stripe\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Stripe\Stripe;
use Stripe\Customer;

class VaultTokenObserver implements ObserverInterface
{
  /** @var CustomerRepositoryInterface */
  private $customerRepository;

  /** @var Config */
  private $config;

  /** @var EncryptorInterface */
  private $encryptor;

  public function __construct(
    Config $config,
    EncryptorInterface $encryptor,
    CustomerRepositoryInterface $customerRepository
  ) {
    $this->encryptor = $encryptor;
    $this->config = $config;
    $this->customerRepository = $customerRepository;
    $this->initCredentials();
  }

  protected function initCredentials() {
    Stripe::setApiKey($this->encryptor->decrypt($this->config->getSecretKey()));
  }

  public function execute(Observer $observer) {
    $token = $observer->getObject();
    if ($token->getIsActive()) {
      return;
    }

    try {
      $customer = $this->customerRepository->getById($token->getCustomerId());
    }catch (\Exception $e) {
      return;
    }

    $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');
    if (!$stripeCustomerId) {
      return;
    }

    try {
      $stripeCustomer = Customer::retrieve($stripeCustomerId->getValue());
      $stripeCustomer->sources->retrieve($token->getGatewayToken())->delete();
    }catch (\Exception $e) {
      return;
    }
  }
}