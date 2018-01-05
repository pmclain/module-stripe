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

class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
  /**
   * @var SubjectReader
   */
  private $subjectReader;

  protected function setUp() {
    $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

    $this->subjectReader = $objectManager->getObject(SubjectReader::class);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage The customerId field does not exist
   */
  public function testReadCustomerIdWithException() {
    $this->subjectReader->readCustomerId([]);
  }

  public function testReadCustomerId() {
    $customerId = 1;
    $this->assertEquals($customerId, $this->subjectReader->readCustomerId(['customer_id' => $customerId]));
  }
}