<?php
namespace tests\Zeedhi\Framework\Session\Storage;


use Zeedhi\Framework\Session\Attribute\SimpleAttribute;
use Zeedhi\Framework\Session\Storage\ArraySession;

class ArraySessionTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var ArraySession
	 */
	private $storage;
	/**
	 * @var SimpleAttribute
	 */
	private $attributes;
	private $data;

	protected function setUp() {
		$this->attributes = new SimpleAttribute();
		$this->data = array(
			$this->attributes->getStorageKey() => array('foo' => 'bar'),
		);
		$this->storage = new ArraySession();
		$this->storage->registerBag($this->attributes);
		$this->storage->setSessionData($this->data);
	}

	protected function tearDown() {
		$this->data = null;
		$this->attributes = null;
		$this->storage = null;
	}

	public function testStart() {
		$this->assertEquals('', $this->storage->getId());
		$this->storage->start();
		$id = $this->storage->getId();
		$this->assertNotEquals('', $id);
		$this->storage->start();
		$this->assertEquals($id, $this->storage->getId());
	}

	public function testRegenerate() {
		$this->storage->start();
		$id = $this->storage->getId();
		$this->storage->regenerate();
		$this->assertNotEquals($id, $this->storage->getId());
		$this->assertEquals(array('foo' => 'bar'), $this->storage->getBag('attributes')->all());
		$id = $this->storage->getId();
		$this->storage->regenerate(true);
		$this->assertNotEquals($id, $this->storage->getId());
		$this->assertEquals(array('foo' => 'bar'), $this->storage->getBag('attributes')->all());
	}

	public function testGetId() {
		$this->assertEquals('', $this->storage->getId());
		$this->storage->start();
		$this->assertNotEquals('', $this->storage->getId());
	}

	public function testDestroy() {
		$property = new \ReflectionProperty($this->storage, 'closed');
		$property->setAccessible(true);

		$this->storage->start();
		$this->assertFalse($property->getValue($this->storage));
		$this->storage->destroy();
		$this->assertTrue($property->getValue($this->storage));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testUnstartedSave() {
		$this->storage->save();
	}

}
