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

namespace Pmclain\Stripe\Gateway\Helper;

use Pmclain\Stripe\Gateway\Config\Config;

class PriceFormatter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Formatter constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param string $price
     * @return string
     */
    public function formatPrice($price)
    {
        $price = sprintf('%.' . ($this->isZeroDecimalCurrency() ? '0' : '2') . 'F', $price);

        return str_replace('.', '', $price);
    }

    /**
     * @return bool
     */
    private function isZeroDecimalCurrency()
    {
        return in_array($this->config->getCurrency(), $this->getZeroDecimalCurrencies(), true);
    }

    /**
     * @return array
     */
    private function getZeroDecimalCurrencies()
    {
        return [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'UGX',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ];
    }
}
