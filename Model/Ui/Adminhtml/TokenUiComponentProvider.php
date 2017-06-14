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
namespace Pmclain\Stripe\Model\Ui\Adminhtml;

use Pmclain\Stripe\Model\Ui\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Framework\UrlInterface;

/**
 * Class TokenUiComponentProvider
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
  /**
   * @var TokenUiComponentInterfaceFactory
   */
  private $componentFactory;

  /**
   * @var \Magento\Framework\UrlInterface
   */
  private $urlBuilder;

  /**
   * @param TokenUiComponentInterfaceFactory $componentFactory
   * @param UrlInterface $urlBuilder
   */
  public function __construct(
    TokenUiComponentInterfaceFactory $componentFactory,
    UrlInterface $urlBuilder
  ) {
    $this->componentFactory = $componentFactory;
    $this->urlBuilder = $urlBuilder;
  }

  /**
   * Get UI component for token
   * @param PaymentTokenInterface $paymentToken
   * @return TokenUiComponentInterface
   */
  public function getComponentForToken(PaymentTokenInterface $paymentToken)
  {
    $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
    $component = $this->componentFactory->create(
      [
        'config' => [
          'code' => ConfigProvider::CC_VAULT_CODE,
          TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
          TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
          'template' => 'Pmclain_Stripe::form/vault.phtml'
        ],
        'name' => Template::class
      ]
    );

    return $component;
  }
}