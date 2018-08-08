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

namespace Pmclain\Stripe\Model\InstantPurchase\CreditCard;

use Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class TokenFormatter implements PaymentTokenFormatterInterface
{
    /**
     * Most used credit card types
     * @var array
     */
    public static $baseCardTypes = [
        'AE' => 'American Express',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'CUP' => 'China Union Pay',
        'MI' => 'Maestro',
    ];

    /**
     * @inheritdoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        $details = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        if (!isset($details['type'], $details['maskedCC'], $details['expirationDate'])) {
            throw new \InvalidArgumentException('Invalid credit card token details.');
        }

        if (isset(self::$baseCardTypes[$details['type']])) {
            $ccType = self::$baseCardTypes[$details['type']];
        } else {
            $ccType = $details['type'];
        }

        $formatted = sprintf(
            '%s: %s, %s: %s (%s: %s)',
            __('Credit Card'),
            $ccType,
            __('ending'),
            $details['maskedCC'],
            __('expires'),
            $details['expirationDate']
        );

        return $formatted;
    }
}
