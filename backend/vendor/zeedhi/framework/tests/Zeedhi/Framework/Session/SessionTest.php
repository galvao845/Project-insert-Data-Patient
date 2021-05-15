<?php
namespace tests\Zeedhi\Framework\Session;

use Zeedhi\Framework\Session\Attribute\SimpleAttribute;
use Zeedhi\Framework\Session\Session;
use Zeedhi\Framework\Session\SessionInterface;
use Zeedhi\Framework\Session\Storage\ArraySession;
use Zeedhi\Framework\Session\Storage\SessionStorageInterface;

class SessionTest extends \PHPUnit\Framework\TestCase {

	/** @var SessionStorageInterface */
	protected $storage;
	/** @var SessionInterface */
	protected $session;

	protected function setUp() {
		$this->storage = new ArraySession();
		$this->session = new Session($this->storage, new SimpleAttribute());
	}

	protected function tearDown() {
		$this->storage = null;
		$this->session = null;
	}

	public function testStart() {
		$this->assertEquals('', $this->session->getId());
		$this->assertTrue($this->session->start());
		$this->assertNotEquals('', $this->session->getId());
	}

	public function testDestroy() {
		$this->storage = $this->getMockBuilder(ArraySession::class)
			->setMethods(array('start', 'destroy', 'isStarted'))
			->disableOriginalConstructor()
			->getMock();
		$this->session = new Session($this->storage, new SimpleAttribute());
		$this->storage->expects($this->once())
			->method('isStarted')
			->willReturn(false);
		$this->storage->expects($this->once())
			->method('start');
		$this->storage->expects($this->once())
			->method('destroy')
			->willReturn(true);
		$this->assertTrue($this->session->destroy());
	}

	public function testDestroyWithoutBeStarted() {
		$this->session->destroy();
		$this->assertTrue($this->session->destroy());
	}

	public function testIsStarted() {
		$this->assertFalse($this->session->isStarted());
		$this->session->start();
		$this->assertTrue($this->session->isStarted());
	}

	public function testSetId() {
		$this->assertEquals('', $this->session->getId());
		$this->session->setId('0123456789abcdef');
		$this->session->start();
		$this->assertEquals('0123456789abcdef', $this->session->getId());
	}

	public function testSetName() {
		$this->assertEquals('MOCKSESSID', $this->session->getName());
		$this->session->setName('session.test.com');
		$this->session->start();
		$this->assertEquals('session.test.com', $this->session->getName());
	}

	public function testGet() {
		// tests defaults
		$this->assertNull($this->session->get('foo'));
		$this->assertEquals(1, $this->session->get('foo', 1));
	}

	/**
	 * @dataProvider setProvider
	 */
	public function testSet($key, $value) {
		$this->session->set($key, $value);
		$this->assertEquals($value, $this->session->get($key));
	}

	/**
	 * @dataProvider setProvider
	 */
	public function testHas($key, $value) {
		$this->session->set($key, $value);
		$this->assertTrue($this->session->has($key));
		$this->assertFalse($this->session->has($key . 'non_value'));
	}

	public function testReplace() {
		$this->session->replace(array('happiness' => 'be good', 'symfony' => 'awesome'));
		$this->assertEquals(array('happiness' => 'be good', 'symfony' => 'awesome'), $this->session->all());
		$this->session->replace(array());
		$this->assertEquals(array(), $this->session->all());
	}

	/**
	 * @dataProvider setProvider
	 */
	public function testAll($key, $value, $result) {
		$this->session->set($key, $value);
		$this->assertEquals($result, $this->session->all());
	}

	/**
	 * @dataProvider setProvider
	 */
	public function testClear($key, $value) {
		$this->session->set('hi', 'fabien');
		$this->session->set($key, $value);
		$this->session->clear();
		$this->assertEquals(array(), $this->session->all());
	}

	public function setProvider() {
		return array(
			array('foo', 'bar', array('foo' => 'bar')),
			array('foo.bar', 'too much beer', array('foo.bar' => 'too much beer')),
			array('great', 'symfony is great', array('great' => 'symfony is great')),
		);
	}

	/**
	 * @dataProvider setProvider
	 */
	public function testRemove($key, $value) {
		$this->session->set('hi.world', 'have a nice day');
		$this->session->set($key, $value);
		$this->session->remove($key);
		$this->assertEquals(array('hi.world' => 'have a nice day'), $this->session->all());
	}

	public function testInvalidate() {
		$this->session->set('invalidate', 123);
		$this->session->invalidate();
		$this->assertEquals(array(), $this->session->all());
	}

	public function testMigrate() {
		$this->session->set('migrate', 321);
		$this->session->migrate();
		$this->assertEquals(321, $this->session->get('migrate'));
	}

	public function testMigrateDestroy() {
		$this->session->set('migrate', 333);
		$this->session->migrate(true);
		$this->assertEquals(333, $this->session->get('migrate'));
	}

	public function testSave() {
		$this->session->start();
		$this->session->save();
	}

	public function testGetId() {
		$this->assertEquals('', $this->session->getId());
		$this->session->start();
		$this->assertNotEquals('', $this->session->getId());
	}

	public function testGetMeta() {
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Storage\MetadataBag', $this->session->getMetadataBag());
	}

}
