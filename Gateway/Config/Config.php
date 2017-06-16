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
namespace Pmclain\Stripe\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
  const KEY_ENVIRONMENT = 'test_mode';
  const KEY_ACTIVE = 'active';
  const KEY_LIVE_PUBLISHABLE_KEY = 'live_publishable_key';
  const KEY_LIVE_SECRET_KEY = 'live_secret_key';
  const KEY_TEST_PUBLISHABLE_KEY = 'test_publishable_key';
  const KEY_TEST_SECRET_KEY = 'test_secret_key';
  const KEY_CURRENCY = 'currency';
  const KEY_CC_TYPES = 'cctypes';
  const KEY_CC_TYPES_STRIPE_MAPPER = 'cctypes_stripe_mapper';
  const KEY_USE_CCV = 'useccv';
  const KEY_ALLOW_SPECIFIC = 'allowspecific';
  const KEY_SPECIFIC_COUNTRY = 'specificcountry';

  /**
   * @return array
   */
  public function getAvailableCardTypes() {
    $ccTypes = $this->getValue(self::KEY_CC_TYPES);

    return !empty($ccTypes) ? explode(',', $ccTypes) : [];
  }

  /**
   * @return array|mixed
   */
  public function getCcTypesMapper() {
    $result = json_decode(
      $this->getValue(self::KEY_CC_TYPES_STRIPE_MAPPER),
      true
    );

    return is_array($result) ? $result : [];
  }

  /**
   * @return mixed
   */
  public function getCurrency() {
    return $this->getValue(self::KEY_CURRENCY);
  }

  /**
   * @return bool
   */
  public function isCcvEnabled() {
    return (bool) $this->getValue(self::KEY_USE_CCV);
  }

  /**
   * @return mixed
   */
  public function getEnvironment() {
    return $this->getValue(Config::KEY_ENVIRONMENT);
  }

  /**
   * @return bool
   */
  public function isActive() {
    return (bool) $this->getValue(self::KEY_ACTIVE);
  }

  /**
   * @return mixed
   */
  public function getPublishableKey() {
    if($this->isTestMode()) {
      return $this->getValue(self::KEY_TEST_PUBLISHABLE_KEY);
    }
    return $this->getValue(self::KEY_LIVE_PUBLISHABLE_KEY);
  }

  /**
   * @return mixed
   */
  public function getSecretKey() {
    if($this->isTestMode()) {
      return $this->getValue(self::KEY_TEST_SECRET_KEY);
    }
    return $this->getValue(self::KEY_LIVE_SECRET_KEY);
  }

  /**
   * @return bool
   */
  public function isTestMode() {
    return (bool) $this->getValue(self::KEY_ENVIRONMENT);
  }
}