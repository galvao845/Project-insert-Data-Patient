<?php
namespace tests\Zeedhi\Framework\Session\Storage;


use Zeedhi\Framework\Session\Attribute\SimpleAttribute;
use Zeedhi\Framework\Session\Storage\FileSession;

class FileSessionTest extends \PHPUnit\Framework\TestCase {
	/**
	 * @var string
	 */
	private $sessionDir;

	/**
	 * @var FileSession
	 */
	protected $storage;

	protected function setUp() {
		$this->sessionDir = sys_get_temp_dir() . '/zeedhiFramework';
		$this->storage = $this->getStorage();
	}

	protected function tearDown() {
		$this->sessionDir = null;
		$this->storage = null;
		array_map('unlink', glob($this->sessionDir . '/*.session'));
		if (is_dir($this->sessionDir)) {
			rmdir($this->sessionDir);
		}
	}

	public function testStart() {
		$this->assertEquals('', $this->storage->getId());
		$this->assertTrue($this->storage->start());
		$id = $this->storage->getId();
		$this->assertNotEquals('', $this->storage->getId());
		$this->assertTrue($this->storage->start());
		$this->assertEquals($id, $this->storage->getId());
	}

	public function testRegenerate() {
		$this->storage->start();
		$this->storage->getBag('attributes')->set('regenerate', 1234);
		$this->storage->regenerate();
		$this->assertEquals(1234, $this->storage->getBag('attributes')->get('regenerate'));
		$this->storage->regenerate(true);
		$this->assertEquals(1234, $this->storage->getBag('attributes')->get('regenerate'));
	}

	public function testGetId() {
		$this->assertEquals('', $this->storage->getId());
		$this->storage->start();
		$this->assertNotEquals('', $this->storage->getId());
	}

	public function testSave() {
		$this->storage->start();
		$id = $this->storage->getId();
		$this->assertNotEquals('108', $this->storage->getBag('attributes')->get('new'));
		$this->storage->getBag('attributes')->set('new', '108');
		$this->storage->save();

		$storage = $this->getStorage();
		$storage->setId($id);
		$storage->start();
		$this->assertEquals('108', $storage->getBag('attributes')->get('new'));
	}

	public function testMultipleInstances() {
		$storage1 = $this->getStorage();
		$storage1->start();
		$storage1->getBag('attributes')->set('foo', 'bar');
		$storage1->save();

		$storage2 = $this->getStorage();
		$storage2->setId($storage1->getId());
		$storage2->start();
		$this->assertEquals('bar', $storage2->getBag('attributes')->get('foo'), 'values persist between instances');
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
	public function testSaveWithoutStart() {
		$storage1 = $this->getStorage();
		$storage1->save();
	}

	private function getStorage() {
		$storage = new FileSession($this->sessionDir);
		$storage->registerBag(new SimpleAttribute());

		return $storage;
	}
}
