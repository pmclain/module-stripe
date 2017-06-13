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
namespace Pmclain\Stripe\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultDataBuilder implements BuilderInterface
{
  /**
   * Additional options in request to gateway
   */
  const OPTIONS = 'options';

  /**
   * The option that determines whether the payment method associated with
   * the successful transaction should be stored in the Vault.
   */
  const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

  /**
   * @inheritdoc
   */
  public function build(array $buildSubject)
  {
    return [
      self::OPTIONS => [
        self::STORE_IN_VAULT_ON_SUCCESS => true
      ]
    ];
  }
}