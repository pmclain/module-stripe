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
namespace Pmclain\Stripe\Helper;

use Pmclain\Stripe\Model\Adminhtml\Source\Cctype as CcTypeSource;

class CcType
{
  private $ccTypes = [];
  private $ccTypeSource;

  public function __construct(
    CcTypeSource $ccTypeSource
  ) {
    $this->ccTypeSource = $ccTypeSource;
  }

  public function getCcTypes() {
    if(!$this->ccTypes) {
      $this->ccTypes = $this->ccTypeSource->toOptionArray();
    }
    return $this->ccTypes;
  }
}