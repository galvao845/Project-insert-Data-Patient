<?php
namespace tests\Zeedhi\Framework\Session\Storage\Handler;


use Zeedhi\Framework\DB\Mongo\Mongo;
use Zeedhi\Framework\Session\Storage\Handler\MongoHandler;

class MongoHandlerTest extends \PHPUnit\Framework\TestCase {

	const MONGO_HOST = '192.168.122.55';
	const MONGO_PORT = '27019';
	const MONGO_DB_NAME = 'testSessionStorage';

	/** @var Mongo */
	private $mongo;
	/** @var MongoHandler */
	private $storage;
	private $options;

	protected function setUp() {
		if (!extension_loaded('mongodb')) {
			$this->markTestSkipped('MongoHandler requires the PHP "mongo" extension.');
		}

		$this->mongo = new Mongo(self::MONGO_HOST, self::MONGO_PORT, 'zhFramework');

		$this->options = array(
			'id_field' => '_id',
			'data_field' => 'data',
			'time_field' => 'time',
			'collection' => 'session-test',
		);

		$this->storage = new MongoHandler($this->mongo, $this->options);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructorShouldThrowExceptionForInvalidOptions() {
		unset($this->options['collection']);
		new MongoHandler($this->mongo, $this->options);
	}

	public function testOpenMethodAlwaysReturnTrue() {
		$this->assertTrue($this->storage->open('test', 'test'), 'The "open" method should always return true');
	}

	public function testCloseMethodAlwaysReturnTrue() {
		$this->assertTrue($this->storage->close(), 'The "close" method should always return true');
	}

	public function testDestroy() {
		$this->assertTrue($this->storage->destroy('foo'));
	}

	public function testWrite() {
		$this->assertTrue($this->storage->write('foo', 'bar'));
	}

	public function testRead() {
		$this->assertTrue($this->storage->write('foo', 'bar'));
		$this->assertEquals('bar', $this->storage->read('foo'));
	}

	public function testGc() {
		$this->assertTrue($this->storage->gc(1));
	}

	public function testGcWhenUsingExpiresField() {
		$this->options = array(
			'id_field' => '_id',
			'data_field' => 'data',
			'time_field' => 'time',
			'collection' => 'session-test',
			'expiry_field' => 'expiresAt',
		);

		$this->storage = new MongoHandler($this->mongo, $this->options);
		$this->assertTrue($this->storage->gc(1));
	}
}
