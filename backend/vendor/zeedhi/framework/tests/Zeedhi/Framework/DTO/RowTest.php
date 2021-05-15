<?php
namespace tests\Zeedhi\Framework\DTO;

use Zeedhi\Framework\DTO\Row;

class RowTest extends \PHPUnit\Framework\TestCase {

	public function testObjectWorkAsArray() {
		$row = array('value' => true, 'value2' => false);
		$rowObject = new Row($row);
		$this->assertEquals(true, $rowObject->get('value'));
		$this->assertEquals(false, $rowObject->get('value2'));
		$rowObject->remove('value2');
		$this->assertEquals(false, $rowObject->has('value2'));
		$rowObject->set('value', false);
		$this->assertEquals(false, $rowObject->get('value'));
	}
}
