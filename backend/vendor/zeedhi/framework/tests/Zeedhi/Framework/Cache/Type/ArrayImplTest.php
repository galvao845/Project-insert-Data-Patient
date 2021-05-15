<?php
namespace tests\Zeedhi\Framework\Cache\Type;

use tests\Zeedhi\Framework\Cache\CacheTest;
use Zeedhi\Framework\Cache\Type\ArrayImpl;

class ArrayImplTest extends CacheTest {

	protected function getCache() {
		return new ArrayImpl();
	}

}
 