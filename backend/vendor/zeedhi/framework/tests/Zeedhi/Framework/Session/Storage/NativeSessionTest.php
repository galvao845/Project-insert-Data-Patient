<?php
namespace tests\Zeedhi\Framework\Session\Storage;

use Zeedhi\Framework\Session\Attribute\SimpleAttribute;
use Zeedhi\Framework\Session\Storage\NativeSession;

class NativeSessionTest extends \PHPUnit\Framework\TestCase {

	private $savePath;

	protected function setUp() {
		$this->markTestSkipped("Can't run tests because headers are sent before session configuration");

		ini_set('session.save_handler', 'files');
		ini_set('session.save_path', $this->savePath = sys_get_temp_dir() . '/zeedhiFramework');
		if (!is_dir($this->savePath)) {
			mkdir($this->savePath);
		}
	}

	protected function tearDown() {
		session_write_close();
		array_map('unlink', glob($this->savePath . '/*'));
		if (is_dir($this->savePath)) {
			rmdir($this->savePath);
		}

		$this->savePath = null;
	}

	/**
	 * @param array $options
	 *
	 * @return NativeSession
	 */
	protected function getStorage(array $options = array()) {
		$storage = new NativeSession($options);
		$storage->registerBag(new SimpleAttribute());

		return $storage;
	}

	/** Fix headers sent before uncomment this test */
//	public function testDestroy() {
//		$storage = $this->getStorage();
//		$property = new \ReflectionProperty($storage, 'closed');
//		$property->setAccessible(true);
//
//		$storage->start();
//		$storage->destroy();
//		$this->assertTrue($property->getValue($storage));
//	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRegisterBagException() {
		$storage = $this->getStorage();
		$storage->getBag('non_existing');
	}
}
