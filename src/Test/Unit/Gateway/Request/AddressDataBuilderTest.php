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

namespace Pmclain\Stripe\Test\Unit\Gateway\Request;

use Pmclain\Stripe\Gateway\Request\AddressDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;

class AddressDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var OrderAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var AddressDataBuilder
     */
    private $builder;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new AddressDataBuilder($this->subjectReaderMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [
            'payment' => null,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        $this->builder->build($buildSubject);
    }

    public function testBuildNoAddresses()
    {
        $this->paymentDataObjectMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn(null);

        $buildSubject = [
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDataObjectMock);

        static::assertEquals([], $this->builder->build($buildSubject));
    }

    /**
     * @param array $addressData
     * @param array $expectedResult
     *
     * @dataProvider dataProviderBuild
     */
    public function testBuild($addressData, $expectedResult)
    {
        $addressMock = $this->getAddressMock($addressData);

        $this->paymentDataObjectMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($addressMock);

        $buildSubject = [
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDataObjectMock);

        self::assertEquals($expectedResult,
            $this->builder->build($buildSubject));
    }

    /**
     * @return array
     */
    public function dataProviderBuild()
    {
        return [
            [
                [
                    'firstname' => 'john',
                    'lastname' => 'doe',
                    'phone' => '555-555-5555',
                    'street_1' => 'street1',
                    'street_2' => 'street2',
                    'city' => 'Chicago',
                    'region_code' => 'IL',
                    'country_id' => 'US',
                    'post_code' => '00000'
                ],
                [
                    AddressDataBuilder::SHIPPING_ADDRESS => [
                        'address' => [
                            AddressDataBuilder::STREET_ADDRESS => 'street1',
                            AddressDataBuilder::EXTENDED_ADDRESS => 'street2',
                            AddressDataBuilder::LOCALITY => 'Chicago',
                            AddressDataBuilder::REGION => 'IL',
                            AddressDataBuilder::POSTAL_CODE => '00000',
                            AddressDataBuilder::COUNTRY_CODE => 'US'
                        ],
                        AddressDataBuilder::NAME => 'john doe',
                        AddressDataBuilder::PHONE => '555-555-5555'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $addressData
     * @return AddressAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAddressMock($addressData)
    {
        $addressMock = $this->createMock(AddressAdapterInterface::class);

        $addressMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($addressData['firstname']);
        $addressMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($addressData['lastname']);
        $addressMock->expects($this->once())
            ->method('getTelephone')
            ->willReturn($addressData['phone']);
        $addressMock->expects($this->once())
            ->method('getStreetLine1')
            ->willReturn($addressData['street_1']);
        $addressMock->expects($this->once())
            ->method('getStreetLine2')
            ->willReturn($addressData['street_2']);
        $addressMock->expects($this->once())
            ->method('getCity')
            ->willReturn($addressData['city']);
        $addressMock->expects($this->once())
            ->method('getRegionCode')
            ->willReturn($addressData['region_code']);
        $addressMock->expects($this->once())
            ->method('getPostcode')
            ->willReturn($addressData['post_code']);
        $addressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn($addressData['country_id']);

        return $addressMock;
    }
}
