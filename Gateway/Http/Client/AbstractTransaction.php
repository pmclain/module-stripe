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
namespace Pmclain\Stripe\Gateway\Http\Client;

use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
  protected $logger;

  protected $customLogger;

  protected $adapter;

  public function __construct(
    LoggerInterface $logger,
    Logger $customLogger,
    StripeAdapter $adapter
  ) {
    $this->logger = $logger;
    $this->customLogger = $customLogger;
    $this->adapter = $adapter;
  }

  public function placeRequest(
    TransferInterface $transferObject
  ) {
    $data = $transferObject->getBody();
    $log = [
      'request' => $data,
      'client' => static::class
    ];
    $response['object'] = [];

    try {
      $response['object'] = $this->process($data);
    }catch (\Exception $e) {
      $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
      $this->logger->critical($message);
      throw new ClientException($message);
    }finally {
      $log['response'] = (array) $response['object'];
      $this->customLogger->debug($log);
    }

    return $response;
  }

  abstract protected function process(array $data);
}