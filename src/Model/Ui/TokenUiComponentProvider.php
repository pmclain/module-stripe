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

use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Json\DecoderInterface;

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
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param UrlInterface $urlBuilder
     * @param DecoderInterface $decoder
     * @param Config $config
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        UrlInterface $urlBuilder,
        DecoderInterface $decoder,
        Config $config
    ) {
        $this->componentFactory = $componentFactory;
        $this->urlBuilder = $urlBuilder;
        $this->jsonDecoder = $decoder;
        $this->config = $config;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = $this->jsonDecoder->decode($paymentToken->getTokenDetails() ?: '{}');
        $jsonDetails['source'] = $this->getSource($paymentToken);

        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigProvider::CC_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                ],
                'name' => 'Pmclain_Stripe/js/view/payment/method-renderer/vault',
            ]
        );

        return $component;
    }

    /**
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    private function getSource($paymentToken)
    {
        if ($this->config->isRequireThreeDSecure()) {
            return $paymentToken->getGatewayToken();
        }

        return '';
    }
}
