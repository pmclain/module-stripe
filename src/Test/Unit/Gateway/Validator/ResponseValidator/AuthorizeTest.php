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

namespace Pmclain\Stripe\Test\Unit\Gateway\Validator\ResponseValidator;

use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pmclain\Stripe\Gateway\Validator\ResponseValidator\Authorize;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class AuthorizeTest extends \PHPUnit\Framework\TestCase
{
    /** @var Authorize */
    private $responseValidator;

    /** @var ResultInterfaceFactory|MockObject */
    private $resultInterfaceFactoryMock;

    /** @var SubjectReader|MockObject */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->resultInterfaceFactoryMock = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseValidator = new Authorize(
            $this->resultInterfaceFactoryMock,
            $this->subjectReaderMock
        );
    }

    /**
     * Run test for validate method
     *
     * @param array $validationSubject
     * @param bool $isValid
     * @param Phrase[] $messages
     * @return void
     *
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate(array $response, $isValid, $messages)
    {
        /** @var ResultInterface|MockObject $resultMock */
        $resultMock = $this->createMock(ResultInterface::class);
        $outcome = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__toArray'])
            ->getMock();
        $outcome->expects($this->once())
            ->method('__toArray')
            ->willReturn($response);

        $validationSubject = [
            'status' => 'succeeded',
            'outcome' => $outcome
        ];

        $this->subjectReaderMock->expects($this->once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willReturn($validationSubject);

        $this->resultInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'isValid' => $isValid,
                'failsDescription' => $messages
            ])
            ->willReturn($resultMock);

        $actualMock = $this->responseValidator->validate($validationSubject);

        $this->assertEquals($resultMock, $actualMock);
    }

    /**
     * @return array
     */
    public function dataProviderTestValidate()
    {
        return [
            [
                ['network_status' => 'approved_by_network'],
                'isValid' => true,
                []
            ],
            [
                ['network_status' => 'declined_by_network'],
                'isValid' => false,
                [__('Transaction has been declined')]
            ]
        ];
    }
}
