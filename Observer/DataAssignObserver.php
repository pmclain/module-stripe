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
namespace Pmclain\Stripe\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
  /**
   * @param Observer $observer
   * @return void
   */
  public function execute(Observer $observer)
  {
    $method = $this->readMethodArgument($observer);
    $data = $this->readDataArgument($observer);
    $paymentInfo = $method->getInfoInstance();
    if (key_exists('cc_token', $data->getDataByKey('additional_data'))) {
      $paymentInfo->setAdditionalInformation(
        'cc_token',
        $data->getDataByKey('additional_data')['cc_token']
      );
    }
  }
}