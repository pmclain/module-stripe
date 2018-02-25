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

namespace Pmclain\Stripe\Test\Unit\Gateway\Validator;

use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pmclain\Stripe\Gateway\Validator\GeneralResponseValidator;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class GeneralResponseValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var GeneralResponseValidator */
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

        $this->responseValidator = new GeneralResponseValidator(
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
    public function testValidate(array $validationSubject, $isValid, $messages)
    {
        /** @var ResultInterface|MockObject $resultMock */
        $resultMock = $this->createMock(ResultInterface::class);

        $this->subjectReaderMock->expects($this->once())
            ->method('readResponseObject')
            ->with($validationSubject)
            ->willReturn($validationSubject['response']['object']);

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
                'validationSubject' => [
                    'response' => [
                        'object' => [
                            'status' => 'succeeded'
                        ]
                    ],
                ],
                'isValid' => true,
                []
            ],
            [
                'validationSubject' => [
                    'response' => [
                        'object' => [
                            'status' => 'failed'
                        ]
                    ]
                ],
                'isValid' => false,
                [
                    __('Stripe error response.')
                ]
            ]
        ];
    }
}
