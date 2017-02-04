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
namespace Pmclain\Stripe\Gateway\Command;

use Pmclain\Stripe\Model\Adapter\StripeAdapter;
use Pmclain\Stripe\Model\Adapter\StripeSearchAdapter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class CaptureStrategyCommand implements CommandInterface
{
  const SALE = 'sale';

  const CAPTURE = 'settlement';

  private $commandPool;
  private $transactionRepository;
  private $filterBuilder;
  private $searchCriteriaBuilder;
  private $subjectReader;
  private $stripeAdapter;

  public function __construct(
    CommandPoolInterface $commandPool,
    TransactionRepositoryInterface $repository,
    FilterBuilder $filterBuilder,
    SearchCriteriaBuilder $searchCriteriaBuilder,
    SubjectReader $subjectReader,
    StripeAdapter $stripeAdapter
  ) {
    $this->commandPool = $commandPool;
    $this->transactionRepository = $repository;
    $this->filterBuilder = $filterBuilder;
    $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    $this->subjectReader = $subjectReader;
    $this->stripeAdapter = $stripeAdapter;
  }

  public function execute(array $commandSubject) {
    $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
    $paymentInfo = $paymentDataObject->getPayment();
    ContextHelper::assertOrderPayment($paymentInfo);

    $command = $this->getCommand($paymentInfo);
    $this->commandPool->get($command)->execute($commandSubject);
  }

  private function getCommand(OrderPaymentInterface $payment) {
    $existsCapture = $this->isExistsCaptureTransaction($payment);
    if(!$payment->getAuthorizationTransaction() && !$existsCapture) {
      return self::SALE;
    }

    if(!$existsCapture) {
      return self::CAPTURE;
    }
  }

  private function isExistsCaptureTransaction(OrderPaymentInterface $payment) {
    $this->searchCriteriaBuilder->addFilters(
      [
        $this->filterBuilder
          ->setField('payment_id')
          ->setValue($payment->getId())
          ->create()
      ]
    );

    $this->searchCriteriaBuilder->addFilters(
      [
        $this->filterBuilder
          ->setField('txn_type')
          ->setValue(TransactionInterface::TYPE_CAPTURE)
          ->create()
      ]
    );

    $searchCriteria = $this->searchCriteriaBuilder->create();

    $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
    return (boolean) $count;
  }
}