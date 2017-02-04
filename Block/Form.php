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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;

class Form extends Cc
{
  protected $gatewayConfig;

  public function __construct(
    Context $context,
    Config $paymentConfig,
    GatewayConfig $gatewayConfig,
    array $data = []
  ) {
    parent::__construct($context, $paymentConfig, $data);
    $this->gatewayConfig = $gatewayConfig;
  }

  public function useCcv() {
    return $this->gatewayConfig->isCcvEnabled();
  }
}