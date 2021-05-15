<?php
namespace tests\Zeedhi\Framework\Cache\Type;

use tests\Zeedhi\Framework\Cache\CacheTest;
use Zeedhi\Framework\Cache\Type\MemcachedImpl;

class MemcachedImplTest extends CacheTest {

	/** @var  \Memcached */
	protected $memcached;

	public function setUp() {
		if (!extension_loaded('memcached')) {
			$this->markTestSkipped('The ' . __CLASS__ . ' requires the use of memcache');
		}
		$this->memcached = new \Memcached();
		$this->memcached->setOption(\Memcached::OPT_COMPRESSION, false);
		$this->memcached->addServer('127.0.0.1', 11211);
	}

	public function tearDown() {
		if ($this->memcached) {
			$this->memcached->flush();
		}
	}

	protected function getCache() {
		return new MemcachedImpl($this->memcached);
	}
}
 