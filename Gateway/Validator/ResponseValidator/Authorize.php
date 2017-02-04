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
namespace Pmclain\Stripe\Gateway\Validator\ResponseValidator;

use Pmclain\Stripe\Gateway\Validator\ResponseValidator;

class Authorize extends ResponseValidator
{
  protected function getResponseValidators() {
    return array_merge(
      parent::getResponseValidators(),
      [
        function ($response) {
          return [
            $response['outcome']->__toArray()['network_status'] === 'approved_by_network',
            [__('Transaction has been declined')]
          ];
        }
      ]
    );
  }
}