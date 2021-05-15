<?php
namespace tests\Zeedhi\Framework\Session\Storage\Handler;


use Zeedhi\Framework\Session\Storage\Handler\NativeFileHandler;
use Zeedhi\Framework\Session\Storage\NativeSession;

class NativeFileHandlerTest extends \PHPUnit\Framework\TestCase {

	public function setUp() {
		$this->markTestSkipped("Can't run tests because headers are sent before session configuration");
	}

	public function testConstruct() {
		$storage = new NativeSession(array('name' => 'TESTING'), new NativeFileHandler(sys_get_temp_dir()));

		if (PHP_VERSION_ID < 50400) {
			$this->assertEquals('files', $storage->getSaveHandler()->getSaveHandlerName());
			$this->assertEquals('files', ini_get('session.save_handler'));
		} else {
			$this->assertEquals('files', $storage->getSaveHandler()->getSaveHandlerName());
			$this->assertEquals('user', ini_get('session.save_handler'));
		}

		$this->assertEquals(sys_get_temp_dir(), ini_get('session.save_path'));
		$this->assertEquals('TESTING', ini_get('session.name'));
	}

	/**
	 * @dataProvider savePathDataProvider
	 */
	public function testConstructSavePath($savePath, $expectedSavePath, $path) {
		$handler = new NativeFileHandler($savePath);
		$this->assertEquals($expectedSavePath, ini_get('session.save_path'));
		$this->assertTrue(is_dir(realpath($path)));

		rmdir($path);
	}

	public function savePathDataProvider() {
		$base = sys_get_temp_dir();

		return array(
			array("$base/foo", "$base/foo", "$base/foo"),
			array("5;$base/foo", "5;$base/foo", "$base/foo"),
			array("5;0600;$base/foo", "5;0600;$base/foo", "$base/foo"),
		);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructException() {
		$handler = new NativeFileHandler('something;invalid;with;too-many-args');
	}

	public function testConstructDefault() {
		$path = ini_get('session.save_path');
		$storage = new NativeSession(array('name' => 'TESTING'), new NativeFileHandler());

		$this->assertEquals($path, ini_get('session.save_path'));
	}
}
