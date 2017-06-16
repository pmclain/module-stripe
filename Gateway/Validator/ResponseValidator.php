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
namespace Pmclain\Stripe\Gateway\Validator;

class ResponseValidator extends GeneralResponseValidator
{
  protected function getResponseValidators() {
    return array_merge(
      parent::getResponseValidators(),
      [
        function ($response) {
          if(isset($response['error'])) {
            return [false, [$response['message']]];
          }
          return [
            in_array(
              $response['status'],
              [
                'succeeded',
                'pending',
                'failed'
              ]
            ),
            [__('Wrong transaction status')]
          ];
        }
      ]
    );
  }
}