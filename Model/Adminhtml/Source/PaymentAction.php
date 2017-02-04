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
namespace Pmclain\Stripe\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Option\ArrayInterface;

class PaymentAction implements ArrayInterface
{
  public function toOptionArray() {
    return [
      [
        'value' => AbstractMethod::ACTION_AUTHORIZE,
        'label' => __('Authorize Only')
      ],
      [
        'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
        'label' => __('Authorize and Capture')
      ]
    ];
  }
}