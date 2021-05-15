<?php
namespace tests\Zeedhi\Framework\HTTP;

use RuntimeException;

use Zeedhi\Framework\HTTP\Header;

class HeaderTest extends \PHPUnit\Framework\TestCase {

	public function testConstructor() {
		$headers = new Header(array('foo' => 'bar'));
		$this->assertTrue($headers->has('foo'));
	}

	public function testKeys() {
		$headers = new Header(array('foo' => 'bar'));
		$keys = $headers->keys();
		$this->assertEquals('foo', $keys[0]);
		$headers->remove('foo');
		$this->assertFalse($headers->has('foo'));
	}

	public function testGetDate() {
		$headers = new Header(array('foo' => 'Tue, 4 Sep 2012 20:00:00 +0200'));
		$headerDate = $headers->getDate('foo');
		$this->assertInstanceOf('DateTime', $headerDate);
		$headerDate = $headers->getDate('test', new \DateTime());
		$this->assertInstanceOf('DateTime', $headerDate);
	}

	public function testGetDateException() {
		$headers = new Header(array('foo' => 'Tue'));
		$this->expectException(RuntimeException::class);
		$headers->getDate('foo');
	}

	public function testAll() {
		$headers = new Header(array('foo' => 'bar'));
		$this->assertEquals(array('foo' => array('bar')), $headers->all(), '->all() gets all the input');
		$headers = new Header(array('FOO' => 'BAR'));
		$this->assertEquals(array('foo' => array('BAR')), $headers->all(), '->all() gets all the input key are lower case');
	}

	public function testReplace() {
		$headers = new Header(array('foo' => 'bar'));
		$headers->replace(array('NOPE' => 'BAR'));
		$this->assertEquals(array('nope' => array('BAR')), $headers->all(), '->replace() replaces the input with the argument');
		$this->assertFalse($headers->has('foo'), '->replace() overrides previously set the input');
	}

	public function testGet() {
		$headers = new Header(array('foo' => 'bar', 'fuzz' => 'bizz'));
		$this->assertEquals('bar', $headers->get('foo'), '->get return current value');
		$this->assertEquals('bar', $headers->get('FoO'), '->get key in case insensitive');
		$this->assertEquals(array('bar'), $headers->get('foo', 'nope', false), '->get return the value as array');
		$this->assertNull($headers->get('none'), '->get unknown values returns null');
		$this->assertEquals('default', $headers->get('none', 'default'), '->get unknown values returns default');
		$this->assertEquals(array('default'), $headers->get('none', 'default', false), '->get unknown values returns default as array');
		$headers->set('foo', 'bor', false);
		$this->assertEquals('bar', $headers->get('foo'), '->get return first value');
		$this->assertEquals(array('bar', 'bor'), $headers->get('foo', 'nope', false), '->get return all values as array');
	}

	public function testSetAssociativeArray() {
		$headers = new Header();
		$headers->set('foo', array('bad-assoc-index' => 'value'));
		$this->assertSame('value', $headers->get('foo'));
		$this->assertEquals(array('value'), $headers->get('foo', 'nope', false), 'assoc indices of multi-valued headers are ignored');
	}

	public function testAllPreserveCase() {
		$headers = new Header();
		$headers->set('Location', 'http://www.zeedhi.com');
		$headers->set('Content-type', 'text/html');
		$allHeaders = $headers->allPreserveCase();
		$this->assertEquals(array('http://www.zeedhi.com'), $allHeaders['Location']);
		$this->assertEquals(array('text/html'), $allHeaders['Content-type']);
	}

	public function testContains() {
		$headers = new Header(array('foo' => 'bar', 'fuzz' => 'bizz'));
		$this->assertTrue($headers->contains('foo', 'bar'), '->contains first value');
		$this->assertTrue($headers->contains('fuzz', 'bizz'), '->contains second value');
		$this->assertFalse($headers->contains('nope', 'nope'), '->contains unknown value');
		$this->assertFalse($headers->contains('foo', 'nope'), '->contains unknown value');
		$headers->set('foo', 'bor', false);
		$this->assertTrue($headers->contains('foo', 'bar'), '->contains first value');
		$this->assertTrue($headers->contains('foo', 'bor'), '->contains second value');
		$this->assertFalse($headers->contains('foo', 'nope'), '->contains unknown value');
	}
}
