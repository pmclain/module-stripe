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
namespace Pmclain\Stripe\Block;

use Pmclain\Stripe\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;
use Magento\Payment\Helper\Data as Helper;
use Pmclain\Stripe\Model\Ui\ConfigProvider;

class Form extends Cc
{
  /** @var GatewayConfig $gatewayConfig */
  protected $gatewayConfig;

  /** @var Helper $paymentDataHelper */
  private $paymentDataHelper;

  public function __construct(
    Context $context,
    Config $paymentConfig,
    GatewayConfig $gatewayConfig,
    Helper $helper,
    array $data = []
  ) {
    parent::__construct($context, $paymentConfig, $data);
    $this->gatewayConfig = $gatewayConfig;
    $this->paymentDataHelper = $helper;
  }

  public function useCcv() {
    return $this->gatewayConfig->isCcvEnabled();
  }

  /**
   * Check if vault enabled
   * @return bool
   */
  public function isVaultEnabled()
  {
    $storeId = $this->_storeManager->getStore()->getId();
    $vaultPayment = $this->getVaultPayment();
    return $vaultPayment->isActive($storeId);
  }

  /**
   * Get configured vault payment for Braintree
   * @return VaultPaymentInterface
   */
  private function getVaultPayment()
  {
    return $this->paymentDataHelper->getMethodInstance(ConfigProvider::CC_VAULT_CODE);
  }
}