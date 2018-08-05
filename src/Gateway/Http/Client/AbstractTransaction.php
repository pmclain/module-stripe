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

namespace Pmclain\Stripe\Gateway\Http\Client;

use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use Pmclain\Stripe\Gateway\Config\Config;
use Magento\Framework\App\ObjectManager;

abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * @var StripeAdapter
     */
    protected $adapter;

    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractTransaction constructor.
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param StripeAdapter $adapter
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        StripeAdapter $adapter,
        Config $config = null
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapter = $adapter;
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @param TransferInterface $transferObject
     * @return mixed
     * @throws ClientException
     */
    public function placeRequest(
        TransferInterface $transferObject
    )
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
            $this->logger->critical($e);
            throw new ClientException($message);
        } finally {
            if ($response['object'] instanceof \Stripe\Error\Base
                || $response['object'] instanceof \Stripe\StripeObject
            ) {
                $log['response'] = $response['object']->__toString();
            } else {
                $log['response'] = $response['object'];
            }

            $this->customLogger->debug($log);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return mixed
     */
    abstract protected function process(array $data);
}
