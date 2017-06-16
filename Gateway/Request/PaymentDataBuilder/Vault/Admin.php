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
namespace Pmclain\Stripe\Gateway\Request\PaymentDataBuilder\Vault;

use Pmclain\Stripe\Gateway\Request\PaymentDataBuilder\Vault;
use Pmclain\Stripe\Gateway\Config\Config;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\Model\Session\Quote;

class Admin extends Vault
{
  /** @var Quote $adminSession */
  private $adminSession;

  /**
   * Admin constructor.
   * @param Config $config
   * @param SubjectReader $subjectReader
   * @param Session $customerSession
   * @param CustomerRepositoryInterface $customerRepository
   * @param Quote $session
   */
  public function __construct(
    Config $config,
    SubjectReader $subjectReader,
    Session $customerSession,
    CustomerRepositoryInterface $customerRepository,
    Quote $session
  ) {
    parent::__construct($config, $subjectReader, $customerSession, $customerRepository);
    $this->adminSession = $session;
  }

  /**
   * @return \Magento\Framework\Api\AttributeInterface|mixed|null
   */
  protected function getStripeCustomerId() {
    $customer = $this->customerRepository->getById($this->adminSession->getCustomerId());
    $stripeCustomerId = $customer->getCustomAttribute('stripe_customer_id');

    if(!$stripeCustomerId) {
      $stripeCustomerId = $this->createNewStripeCustomer($customer->getEmail());
      $customer->setCustomAttribute('stripe_customer_id', $stripeCustomerId);

      $this->customerRepository->save($customer);
    }

    return $stripeCustomerId;
  }
}