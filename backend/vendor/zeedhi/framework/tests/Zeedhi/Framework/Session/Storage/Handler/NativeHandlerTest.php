<?php
namespace tests\Zeedhi\Framework\Session\Storage\Handler;

use Zeedhi\Framework\Session\Storage\Handler\NativeHandler;

class NativeHandlerTest extends \PHPUnit\Framework\TestCase {

	public function testConstruct() {
		$handler = new NativeHandler();

		if (PHP_VERSION_ID < 50400) {
			$this->assertFalse($handler instanceof \SessionHandler);
			$this->assertTrue($handler instanceof NativeHandler);
		} else {
			$this->assertTrue($handler instanceof \SessionHandler);
			$this->assertTrue($handler instanceof NativeHandler);
		}
	}

}
