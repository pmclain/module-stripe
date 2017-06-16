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
namespace Pmclain\Stripe\Gateway\Response;

use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use Pmclain\Stripe\Gateway\Config\Config;

class VaultDetailsHandler implements HandlerInterface
{
  /**
   * @var CreditCardTokenFactory
   */
  protected $paymentTokenFactory;

  /**
   * @var OrderPaymentExtensionInterfaceFactory
   */
  protected $paymentExtensionFactory;

  /**
   * @var SubjectReader
   */
  protected $subjectReader;

  /** @var Config */
  protected $config;

  /**
   * Constructor
   *
   * @param CreditCardTokenFactory $paymentTokenFactory
   * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
   * @param Config $config
   * @param SubjectReader $subjectReader
   */
  public function __construct(
    CreditCardTokenFactory $paymentTokenFactory,
    OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
    Config $config,
    SubjectReader $subjectReader
  ) {
    $this->paymentTokenFactory = $paymentTokenFactory;
    $this->paymentExtensionFactory = $paymentExtensionFactory;
    $this->subjectReader = $subjectReader;
    $this->config = $config;
  }

  /**
   * @inheritdoc
   */
  public function handle(array $handlingSubject, array $response)
  {
    $paymentDO = $this->subjectReader->readPayment($handlingSubject);
    $transaction = $this->subjectReader->readTransaction($response);
    $payment = $paymentDO->getPayment();

    if(!$payment->getAdditionalInformation('is_active_payment_token_enabler')) {
      return;
    }

    $paymentToken = $this->getVaultPaymentToken($transaction);
    if (null !== $paymentToken) {
      $extensionAttributes = $this->getExtensionAttributes($payment);
      $extensionAttributes->setVaultPaymentToken($paymentToken);
    }
  }

  /**
   * @param $transaction
   * @return PaymentTokenInterface|null
   */
  private function getVaultPaymentToken($transaction)
  {
    // Check token existing in gateway response
    $source = $transaction['source']->__toArray();
    if (!isset($source['id'])) {
      return null;
    }

    /** @var PaymentTokenInterface $paymentToken */
    $paymentToken = $this->paymentTokenFactory->create();
    $paymentToken->setGatewayToken($source['id']);
    $paymentToken->setExpiresAt($this->getExpirationDate($source));

    $paymentToken->setTokenDetails($this->convertDetailsToJSON([
      'type' => $this->getCreditCardType($source['brand']),
      'maskedCC' => $source['last4'],
      'expirationDate' => $source['exp_month'] . '/' . $source['exp_year']
    ]));

    return $paymentToken;
  }

  /**
   * @param array $source
   * @return string
   */
  private function getExpirationDate($source)
  {
    $expDate = new \DateTime(
      $source['exp_year']
      . '-'
      . $source['exp_month']
      . '-'
      . '01'
      . ' '
      . '00:00:00',
      new \DateTimeZone('UTC')
    );
    $expDate->add(new \DateInterval('P1M'));
    return $expDate->format('Y-m-d 00:00:00');
  }

  /**
   * Convert payment token details to JSON
   * @param array $details
   * @return string
   */
  private function convertDetailsToJSON($details)
  {
    $json = \Zend_Json::encode($details);
    return $json ? $json : '{}';
  }

  /**
   * Get type of credit card mapped from Stripe
   *
   * @param string $type
   * @return array
   */
  private function getCreditCardType($type)
  {
    $replaced = str_replace(' ', '-', strtolower($type));
    $mapper = $this->config->getCctypesMapper();

    return $mapper[$replaced];
  }

  /**
   * Get payment extension attributes
   * @param InfoInterface $payment
   * @return OrderPaymentExtensionInterface
   */
  private function getExtensionAttributes(InfoInterface $payment)
  {
    $extensionAttributes = $payment->getExtensionAttributes();
    if (null === $extensionAttributes) {
      $extensionAttributes = $this->paymentExtensionFactory->create();
      $payment->setExtensionAttributes($extensionAttributes);
    }
    return $extensionAttributes;
  }
}