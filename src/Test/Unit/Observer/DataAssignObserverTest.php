<?php

namespace Pmclain\Stripe\Test\Unit\Observer;

use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\Stripe\Observer\DataAssignObserver;
use Magento\Quote\Model\Quote\Payment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;

class DataAssignObserverTest extends TestCase
{
    /**
     * @var DataObject
     */
    private $data;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Observer|MockObject
     */
    private $observer;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var DataAssignObserver
     */
    private $dataAssigner;

    protected function setUp()
    {
        $this->paymentMock = $this->createMock(Payment::class);

        $this->data = new DataObject([
            'additional_data' => [
                'cc_exp_month' => '12',
                'cc_exp_year' => '2022',
                'cc_last4' => '1111',
                'cc_type' => 'Visa',
                'cc_token' => 'tkn_123',
                'cc_src' => 'crd_123',
                'three_d_src' => 'tds_123',
                'three_d_client_secret' => 'client_secret',
            ],
        ]);

        $this->event = new Event([
            'data' => $this->data,
        ]);

        $this->observer = new Observer([
            'event' => $this->event,
            'payment_model' => $this->paymentMock,
        ]);

        $this->dataAssigner = new DataAssignObserver();
    }

    public function testExecute()
    {
        $this->paymentMock->expects($this->exactly(4))
            ->method('setAdditionalInformation');

        $this->dataAssigner->execute($this->observer);
    }
}
