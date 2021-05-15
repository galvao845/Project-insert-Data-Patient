<?php
namespace tests\Zeedhi\Framework\Cache;


abstract class CacheTest extends \PHPUnit\Framework\TestCase {


	public function testBasicCrudOperations() {
		$cache = $this->getCache();

		$this->assertTrue($cache->save('key', 'value'));
		$this->assertTrue($cache->contains('key'));
		$this->assertEquals('value', $cache->fetch('key'));

		$this->assertTrue($cache->save('key', 'value-changed'));
		$this->assertTrue($cache->contains('key'));
		$this->assertEquals('value-changed', $cache->fetch('key'));

		$this->assertTrue($cache->delete('key'));
		$this->assertFalse($cache->contains('key'));
		$this->expectException('Exception');
		$this->assertFalse($cache->fetch('key'));

	}

	public function provideValues() {
		return array(
			'array' => array(array('one', 2, 3.0)),
			'string' => array('value'),
			'integer' => array(1),
			'float' => array(1.5),
			'object' => array(new \ArrayObject()),
			'null' => array(null),
		);
	}

}
