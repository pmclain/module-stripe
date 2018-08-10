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

namespace Pmclain\Stripe\Controller\ThreeDSecure;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Pmclain\Stripe\Model\Helper\OrderPlace;

class Redirect extends Action
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var OrderPlace
     */
    private $orderPlace;

    public function __construct(
        Context $context,
        Session $session,
        OrderPlace $orderPlace
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->orderPlace = $orderPlace;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $quote = $this->session->getQuote();

        try {
            $this->validateQuote($quote);

            $this->orderPlace->execute(
                $quote,
                $this->getRequest()->getParam('source'),
                $this->getRequest()->getParam('client_secret')
            );

            $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
        } catch (\InvalidArgumentException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        }

        return $resultRedirect;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    private function validateQuote($quote)
    {
        if (!$quote || !$quote->getItemsCount()) {
            throw new \InvalidArgumentException(__('We can\'t initialize checkout.'));
        }
    }
}
