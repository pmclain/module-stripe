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
namespace Pmclain\Stripe\Block\Customer;

use Pmclain\Stripe\Model\Ui\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

class CardRenderer extends AbstractCardRenderer
{
  /**
   * Can render specified token
   *
   * @param PaymentTokenInterface $token
   * @return boolean
   */
  public function canRender(PaymentTokenInterface $token)
  {
    return $token->getPaymentMethodCode() === ConfigProvider::CODE;
  }

  /**
   * @return string
   */
  public function getNumberLast4Digits()
  {
    return $this->getTokenDetails()['maskedCC'];
  }

  /**
   * @return string
   */
  public function getExpDate()
  {
    return $this->getTokenDetails()['expirationDate'];
  }

  /**
   * @return string
   */
  public function getIconUrl()
  {
    return $this->getIconForType($this->getTokenDetails()['type'])['url'];
  }

  /**
   * @return int
   */
  public function getIconHeight()
  {
    return $this->getIconForType($this->getTokenDetails()['type'])['height'];
  }

  /**
   * @return int
   */
  public function getIconWidth()
  {
    return $this->getIconForType($this->getTokenDetails()['type'])['width'];
  }
}
