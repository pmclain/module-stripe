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
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;

class VaultDetailsHandler implements HandlerInterface
{
  /**
   * @var PaymentTokenInterfaceFactory
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

  /**
   * Constructor
   *
   * @param PaymentTokenInterfaceFactory $paymentTokenFactory
   * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
   * @param SubjectReader $subjectReader
   */
  public function __construct(
    PaymentTokenInterfaceFactory $paymentTokenFactory,
    OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
    SubjectReader $subjectReader
  ) {
    $this->paymentTokenFactory = $paymentTokenFactory;
    $this->paymentExtensionFactory = $paymentExtensionFactory;
    $this->subjectReader = $subjectReader;
  }

  /**
   * @inheritdoc
   */
  public function handle(array $handlingSubject, array $response)
  {
    $paymentDO = $this->subjectReader->readPayment($handlingSubject);
    $transaction = $this->subjectReader->readTransaction($response);
    $payment = $paymentDO->getPayment();

    // add vault payment token entity to extension attributes
    $paymentToken = $this->getVaultPaymentToken($transaction);
    if (null !== $paymentToken) {
      $extensionAttributes = $this->getExtensionAttributes($payment);
      $extensionAttributes->setVaultPaymentToken($paymentToken);
    }
  }

  private function getVaultPaymentToken($transaction)
  {
    //TODO: this won't actually work in the current state
    // Check token existing in gateway response
    $token = $transaction->creditCardDetails->token;
    if (empty($token)) {
      return null;
    }

    /** @var PaymentTokenInterface $paymentToken */
    $paymentToken = $this->paymentTokenFactory->create();
    $paymentToken->setGatewayToken($token);
    $paymentToken->setExpiresAt($this->getExpirationDate($transaction));

    $paymentToken->setTokenDetails($this->convertDetailsToJSON([
      'type' => $this->getCreditCardType($transaction->creditCardDetails->cardType),
      'maskedCC' => $transaction->creditCardDetails->last4,
      'expirationDate' => $transaction->creditCardDetails->expirationDate
    ]));

    return $paymentToken;
  }
}