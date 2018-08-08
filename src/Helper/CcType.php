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

namespace Pmclain\Stripe\Helper;

use Pmclain\Stripe\Model\Adminhtml\Source\Cctype as CcTypeSource;

class CcType
{
    /**
     * @var array
     */
    private $ccTypes = [];

    /**
     * @var CcTypeSource
     */
    private $ccTypeSource;

    /**
     * CcType constructor.
     * @param CcTypeSource $ccTypeSource
     */
    public function __construct(
        CcTypeSource $ccTypeSource
    ) {
        $this->ccTypeSource = $ccTypeSource;
    }

    /**
     * @return array
     */
    public function getCcTypes()
    {
        if (!$this->ccTypes) {
            $this->ccTypes = $this->ccTypeSource->toOptionArray();
        }
        return $this->ccTypes;
    }
}
