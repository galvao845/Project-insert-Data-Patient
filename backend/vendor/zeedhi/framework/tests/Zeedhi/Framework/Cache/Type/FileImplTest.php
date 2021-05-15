<?php
namespace tests\Zeedhi\Framework\Cache\Type;

use tests\Zeedhi\Framework\Cache\CacheTest;
use Zeedhi\Framework\Cache\Type\FileImpl;

class FileImplTest extends CacheTest {

	protected function getCache() {
		return new FileImpl(__DIR__);
	}

	public function testSaveWithLifeTime() {
		$cache = $this->getCache();
		$cache->save('test', 'cache with lifetime', -1);
		$this->expectException('Exception');
		$cache->fetch('test');
	}

}