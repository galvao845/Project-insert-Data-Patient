<?php
namespace tests\Zeedhi\Framework\Cache\Type;

use tests\Zeedhi\Framework\Cache\CacheTest;
use Zeedhi\Framework\Cache\Type\MongoImpl;
use Zeedhi\Framework\DB\Mongo\Mongo;

class MongoImplTest extends CacheTest {

	const MONGO_HOST = "192.168.122.55";
	const MONGO_PORT = "27019";
	const MONGO_DBNAME = "zhFramework";

	/** @var Mongo */
	protected $mongo;
	/** @var  MongoImpl */
	protected $mongoCache;

	public function setUp() {
		if (!extension_loaded('mongodb')) {
			$this->markTestSkipped('The ' . __CLASS__ . ' requires the use of mongo extension.');
		}

		$this->mongo = new Mongo(self::MONGO_HOST, self::MONGO_PORT, self::MONGO_DBNAME);
		$this->mongoCache = new MongoImpl($this->mongo);
		$this->mongoCache->setCollectionName('zhCache');
	}

	public function tearDown() {
		if ($this->mongo) {
			$this->mongo->dropCollection($this->mongoCache->getCollectionName());
		}
	}

	protected function getCache() {
		return $this->mongoCache;
	}

	public function testSaveWithLifeTime() {
		$cache = $this->getCache();
		$cache->save('test', 'cache with lifetime', -1);
		$this->expectException('Exception');
		$cache->fetch('test');
	}


}
