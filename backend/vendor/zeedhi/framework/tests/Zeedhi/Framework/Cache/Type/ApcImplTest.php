<?php
namespace tests\Zeedhi\Framework\Cache\Type;

use tests\Zeedhi\Framework\Cache\CacheTest;
use Zeedhi\Framework\Cache\Type\ApcImpl;

class ApcImplTest extends CacheTest {

	protected function setUp() {
		if (!extension_loaded('apc') || false === @apc_cache_info()) {
			$this->markTestSkipped('The ' . __CLASS__ . ' requires the use of APC');
		}
	}

	protected function getCache() {
		return new ApcImpl();
	}

}