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

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     * @param ScopeConfigInterface $configInterface
     */
    public function __construct(
        ScopeConfigInterface $configInterface
    ) {
        $this->config = $configInterface;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'publishableKey' => $this->getPublishableKey(),
                    'vaultCode' => self::CC_VAULT_CODE,
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getPublishableKey()
    {
        if ($this->isTestMode()) {
            return $this->getStoreConfig(Config::KEY_TEST_PUBLISHABLE_KEY);
        }
        return $this->getStoreConfig(Config::KEY_LIVE_PUBLISHABLE_KEY);
    }

    /**
     * @return int
     */
    protected function isTestMode()
    {
        return (int)$this->getStoreConfig(Config::KEY_ENVIRONMENT);
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function getStoreConfig($value)
    {
        return $this->config->getValue(
            'payment/pmclain_stripe/' . $value,
            ScopeInterface::SCOPE_STORE
        );
    }
}
