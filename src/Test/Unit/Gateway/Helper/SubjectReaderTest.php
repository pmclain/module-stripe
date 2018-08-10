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

namespace Pmclain\Stripe\Test\Unit\Gateway\Helper;

use InvalidArgumentException;
use Pmclain\Stripe\Gateway\Helper\SubjectReader;
use Stripe\Error\Card;
use Stripe\Charge;

class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Card|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cardErrorMock;

    /**
     * @var Charge|\PHPUnit_Framework_MockObject_MockObject
     */
    private $chargeMock;

    protected function setUp()
    {
        $this->cardErrorMock = $this->createMock(Card::class);
        $this->chargeMock = $this->createMock(Charge::class);

        $this->subjectReader = new SubjectReader();
    }

    public function testReadResponseObject()
    {
        $this->chargeMock->method('__toArray')->willReturn([]);
        $result = $this->subjectReader->readResponseObject([
            'response' => [
                'object' => $this->chargeMock,
            ],
        ]);

        $this->assertEquals([], $result);
    }

    public function testReadResponseObjectWithError()
    {
        $result = $this->subjectReader->readResponseObject([
            'response' => [
                'object' => $this->cardErrorMock,
            ],
        ]);

        $this->assertArrayHasKey('error', $result);
        $this->assertTrue($result['error']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadResponseObjectWithException()
    {
        $this->subjectReader->readResponseObject(['response' => null]);
    }

    public function testReadTransaction()
    {
        $this->chargeMock->method('__toArray')->willReturn([]);
        $result = $this->subjectReader->readTransaction([
            'object' => $this->chargeMock,
        ]);

        $this->assertEquals([], $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadTransactionWithException()
    {
        $this->subjectReader->readTransaction(['object' => null]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The customerId field does not exist
     */
    public function testReadCustomerIdWithException()
    {
        $this->subjectReader->readCustomerId([]);
    }

    public function testReadCustomerId()
    {
        $customerId = 1;
        $this->assertEquals(
            $customerId,
            $this->subjectReader->readCustomerId(['customer_id' => $customerId])
        );
    }
}
