<?php
namespace tests\Zeedhi\Framework\Cache\Type;

use tests\Zeedhi\Framework\Cache\CacheTest;
use Zeedhi\Framework\Cache\Type\ZendDataImpl;

class ZendImplTest extends CacheTest {

	protected function setUp() {
		if (!function_exists('zend_shm_cache_fetch') || (php_sapi_name() != 'apache2handler')) {
			$this->markTestSkipped('The ' . __CLASS__ .' requires the use of Zend Data Cache which only works in apache2handler SAPI');
		}
	}

	protected function getCache() {
		return new ZendDataImpl();
	}
}
 