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
namespace Pmclain\Stripe\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Pmclain\Stripe\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
  const CODE = 'pmclain_stripe';
  const CC_VAULT_CODE = 'pmclain_stripe_vault';

  protected $_config;

  public function __construct(
    ScopeConfigInterface $configInterface
  ){
    $this->_config = $configInterface;
  }

  public function getConfig()
  {
    return [
      'payment' => [
        self::CODE => [
          'publishableKey' => $this->getPublishableKey(),
          'vaultCode' => self::CC_VAULT_CODE,
        ]
      ]
    ];
  }

  public function getPublishableKey() {
    if ($this->_isTestMode()) {
      return $this->_getConfig(Config::KEY_TEST_PUBLISHABLE_KEY);
    }
    return $this->_getConfig(Config::KEY_LIVE_PUBLISHABLE_KEY);
  }

  protected function _isTestMode() {
    return $this->_getConfig(Config::KEY_ENVIRONMENT);
  }

  protected function _getConfig($value) {
    return $this->_config->getValue('payment/pmclain_stripe/' . $value, ScopeInterface::SCOPE_STORE);
  }
}