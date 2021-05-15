<?php
namespace tests\Zeedhi\Framework\Session\Storage\Handler;

use Zeedhi\Framework\Session\Storage\Handler\MemcachedHandler;

class MemcachedHandlerTest extends \PHPUnit\Framework\TestCase {

	const PREFIX = 'prefix_';
	const TTL = 1000;

	/** @var  MemcachedHandler */
	protected $storage;
	/** @var  \PHPUnit_Framework_MockObject_MockObject */
	protected $memcached;

	protected function setUp() {
		if (!class_exists('Memcached')) {
			$this->markTestSkipped('Skipped tests Memcached class is not present');
		}

		if (version_compare(phpversion('memcached'), '2.2.0', '<')) {
			$this->markTestSkipped('Tests can only be run with memcached extension 2.2.0 or lower');
		}

		$this->memcached = $this->createMock('Memcached');
		$this->storage = new MemcachedHandler(
			$this->memcached,
			array('prefix' => self::PREFIX, 'expiretime' => self::TTL)
		);
	}

	protected function tearDown() {
		$this->memcached = null;
		$this->storage = null;
	}

	public function testOpenSession() {
		$this->assertTrue($this->storage->open('', ''));
	}

	public function testCloseSession() {
		$this->assertTrue($this->storage->close());
	}

	public function testReadSession() {
		$this->memcached
			->expects($this->once())
			->method('get')
			->with(self::PREFIX . 'id');

		$this->assertEquals('', $this->storage->read('id'));
	}

	public function testWriteSession() {
		$this->memcached
			->expects($this->once())
			->method('set')
			->with(self::PREFIX . 'id', 'data', $this->equalTo(time() + self::TTL, 2))
			->will($this->returnValue(true));

		$this->assertTrue($this->storage->write('id', 'data'));
	}

	public function testDestroySession() {
		$this->memcached
			->expects($this->once())
			->method('delete')
			->with(self::PREFIX . 'id')
			->will($this->returnValue(true));

		$this->assertTrue($this->storage->destroy('id'));
	}

	public function testGcSession() {
		$this->assertTrue($this->storage->gc(123));
	}

	/**
	 * @dataProvider getOptionFixtures
	 */
	public function testSupportedOptions($options, $supported) {
		try {
			new MemcachedHandler($this->memcached, $options);
			$this->assertTrue($supported);
		} catch (\InvalidArgumentException $e) {
			$this->assertFalse($supported);
		}
	}

	public function getOptionFixtures() {
		return array(
			array(array('prefix' => 'session'), true),
			array(array('expiretime' => 100), true),
			array(array('prefix' => 'session', 'expiretime' => 200), true),
			array(array('expiretime' => 100, 'foo' => 'bar'), false),
		);
	}

	public function testGetConnection() {
		$method = new \ReflectionMethod($this->storage, 'getMemcached');
		$method->setAccessible(true);

		$this->assertInstanceOf('\Memcached', $method->invoke($this->storage));
	}

}
