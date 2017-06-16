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
 */namespace Pmclain\Stripe\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Pmclain\Stripe\Gateway\Validator\ResponseValidator;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ResponseValidatorTest extends \PHPUnit_Framework_TestCase
{
  /** @var ResponseValidator */
  private $responseValidator;

  /** @var ResultInterfaceFactory|MockObject */
  private $resultInterFaceFactory;

  /** @var SubjectReader|MockObject */
  private $subjectReader;

  protected function setUp() {
    $this->resultInterfaceFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
      ->disableOriginalConstructor()
      ->setMethods(['create'])
      ->getMock();
    $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->responseValidator = new ResponseValidator(
      $this->resultInterfaceFactory,
      $this->subjectReader
    );
  }

  /** @expectedException \InvalidArgumentException */
  public function testValidateReadResponseException() {
    $subject = ['response' => null];

    $this->subjectReader->expects($this->once())
      ->method('readResponseObject')
      ->with($subject)
      ->willThrowException(new \InvalidArgumentException());

    $this->responseValidator->validate($subject);
  }

  /** @expectedException \InvalidArgumentException */
  public function testValidateReadResponseObjectException() {
    $subject = ['reponse' => ['object' => null]];

    $this->subjectReader->expects($this->once())
      ->method('readResponseObject')
      ->with($subject)
      ->willThrowException(new \InvalidArgumentException());

    $this->responseValidator->validate($subject);
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
    /** @var ResultInterface|MockObject $result */
    $result = $this->getMock(ResultInterface::class);

    $this->subjectReader->expects($this->once())
      ->method('readResponseObject')
      ->with($validationSubject)
      ->willReturn($validationSubject['response']['object']);

    $this->resultInterfaceFactory->expects($this->once())
      ->method('create')
      ->with([
        'isValid' => $isValid,
        'failsDescription' => $messages
      ])
      ->willReturn($result);

    $actual = $this->responseValidator->validate($validationSubject);

    $this->assertEquals($result, $actual);
  }

  public function dataProviderTestValidate() {
    $succeed = ['object' => ['status' => 'succeeded']];
    $pending = ['object' => ['status' => 'pending']];
    $failed = ['object' => ['status' => 'failed']];
    $invalid = ['object' => ['status' => null]];

    return [
      [
        ['response' => $succeed],
        'isValid' => true,
        []
      ],
      [
        ['response' => $pending],
        'isValid' => false,
        [__('Stripe error response.')]
      ],
      [
        ['response' => $failed],
        'isValid' => false,
        [__('Stripe error response.')],
      ],
      [
        ['response' => $invalid],
        'isValid' => false,
        [
          __('Stripe error response.')
        ]
      ]
    ];
  }
}
