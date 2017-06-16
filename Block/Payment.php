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

use Pmclain\Stripe\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Payment
 */
class Payment extends Template
{
  /**
   * @var ConfigProvider
   */
  private $config;

  /**
   * Constructor
   *
   * @param Context $context
   * @param ConfigProvider $config
   * @param array $data
   */
  public function __construct(
    Context $context,
    ConfigProvider $config,
    array $data = []
  ) {
    parent::__construct($context, $data);
    $this->config = $config;
  }

  /**
   * @return string
   */
  public function getPaymentConfig()
  {
    $payment = $this->config->getConfig()['payment'];
    $config = $payment[$this->getCode()];
    $config['code'] = $this->getCode();
    return json_encode($config, JSON_UNESCAPED_SLASHES);
  }

  /**
   * @return string
   */
  public function getCode()
  {
    return ConfigProvider::CODE;
  }
}
