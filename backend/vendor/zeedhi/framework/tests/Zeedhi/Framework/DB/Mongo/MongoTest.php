<?php
namespace tests\Zeedhi\Framework\DB\Mongo;

use Zeedhi\Framework\DB\Mongo\Mongo;

class MongoTest extends \PHPUnit\Framework\TestCase {

    const THE_BASS = 'THE_BASS';
    const MONGO_HOST = '192.168.120.32';
    const MONGO_PORT = '27019';
    const MONGO_DB_NAME = 'lambda';

    protected static $objects = [
        ["key" => 0, "text" => "zero"],
        ["key" => 1, "text" => "um"],
        ["key" => 2, "text" => "dois"],
        ["key" => 3, "text" => "tres"],
        ["key" => 4, "text" => "quatro"],
        ["key" => 5, "text" => "cinco"],
        ["key" => 6, "text" => "seis"]
    ];

    protected $extensionLoaded;
    protected $mongoDbServerOnline = true;
    /** @var Mongo */
    protected $mongo;

    protected function setUp() {
        $this->extensionLoaded = extension_loaded('mongodb');
        if (!$this->extensionLoaded) {
            $this->markTestSkipped('The tests require mongo extension');
        }

        if ($this->mongoDbServerOnline) {
            try {
                $this->mongo = new Mongo(self::MONGO_HOST, self::MONGO_PORT, self::MONGO_DB_NAME);
                foreach (self::$objects as $object) {
                    $this->mongo->insert(self::THE_BASS, $object);
                }
            } catch (\Exception $e) {
                $this->mongoDbServerOnline = false;
            }
        }

        if (!$this->mongoDbServerOnline) {
            $this->markTestSkipped('The tests require mongo db server be online');
        }
    }

    protected function tearDown() {
        if ($this->extensionLoaded && $this->mongoDbServerOnline) {
            $this->mongo->dropCollection(self::THE_BASS);
        }
    }

    public function testSave() {
        $object = ["key" => 7, "text" => "sete"];
        $this->mongo->insert(self::THE_BASS, $object);
        $result = $this->mongo->find(self::THE_BASS, ["key" => 7]);
        $foundObject = [
            "key" => $result[0]["key"],
            "text" => $result[0]["text"]
        ];
        $this->assertEquals($object, $foundObject);
    }

    public function testFind() {
        $result = $this->mongo->find(self::THE_BASS, ["key" => 3]);
        $foundObject = [
            "key" => $result[0]["key"],
            "text" => $result[0]["text"]
        ];
        $this->assertEquals(self::$objects[3], $foundObject);
    }

    public function testFindSorted() {
        $result = $this->mongo->find(self::THE_BASS, [], ['key' => -1]);
        $foundObject = [
            "key" => $result[0]["key"],
            "text" => $result[0]["text"]
        ];
        $this->assertEquals(self::$objects[6], $foundObject);
    }

    public function testFindLimited() {
        $result = $this->mongo->find(self::THE_BASS, [], null, 2);
        $this->assertCount(2, $result);
    }

    public function testUpdate() {
        $object = ['$set' => ['text' => "three"]];
        $this->mongo->update(self::THE_BASS, ["key" => 3], $object, true);
        $result = $this->mongo->find(self::THE_BASS, ["key" => 3]);
        $foundObject = [
            "key" => $result[0]["key"],
            "text" => $result[0]["text"]
        ];
        
        $this->assertContains($object['$set']['text'], $foundObject);
    }

    public function testRemove() {
        $this->mongo->remove(self::THE_BASS, ["key" => 3]);
        $result = $this->mongo->find(self::THE_BASS, ["key" => 3]);
        $this->assertTrue(empty($result));
    }

    public function testUpdateMultiples () {
        $criteria = [
            'key' => ['$lt' => 3]
        ];
        $update = [
            '$set' => [
                'text' => 'menor que 3'
            ]
        ];
        $upsert = false;
        $multi = true;
        $result = $this->mongo->update(self::THE_BASS, $criteria, $update, $upsert, $multi);
        $this->assertEquals(3, $result->getModifiedCount());
    }

    public function testAggregate() {
        $object = ["key" => 7, "text" => "seis"];
        $this->mongo->insert(self::THE_BASS, $object);
        $criteria = [
            [
                '$group' => [
                    "_id" =>'$text',
                    "id_sum" => ['$sum' => '$key']
                ]
            ]
        ];
        $result = $this->mongo->aggregate(self::THE_BASS, $criteria);
        usort($result, function($a, $b) { return $a['id_sum'] <=> $b['id_sum']; });
        $resultExpected = [
            ['_id' => 'zero',   'id_sum' => 0],
            ['_id' => 'um',     'id_sum' => 1],
            ['_id' => 'dois',   'id_sum' => 2],
            ['_id' => 'tres',   'id_sum' => 3],
            ['_id' => 'quatro', 'id_sum' => 4],
            ['_id' => 'cinco',  'id_sum' => 5],
            ['_id' => 'seis',   'id_sum' => 13],
        ];

        $this->assertEquals($result, $resultExpected);
    }

    public function testFailAggregate() {
        $object = ["key" => 7, "text" => "seis"];
        $this->mongo->insert(self::THE_BASS, $object);
        $criteria = [
            [
                '$group' => [
                    "_id" => ['$exists' => '$text'],
                    "id_sum" => ['$sum' => '$key']
                ]
            ]
        ];

        $this->expectException('\Zeedhi\Framework\DB\Mongo\Exception', "Error while executing aggregate: Unrecognized expression '\$exists'");
        $this->mongo->aggregate(self::THE_BASS, $criteria);
    }

    public function testDbNotSet() {
        $this->expectException('Zeedhi\Framework\DB\Mongo\Exception', '$databaseName is invalid: ');
        $mongo = new Mongo(self::MONGO_HOST, self::MONGO_PORT);
        $mongo->find(self::THE_BASS);
    }
    
    public function testDbPostObjectCreationSet() {
        $mongo = new Mongo(self::MONGO_HOST, self::MONGO_PORT);
        $mongo->setDbByName(self::MONGO_DB_NAME);
        $result = $mongo->find(self::THE_BASS);
        $this->assertCount(7, $result);
    }

    public function testExecuteCommand() {
        $buildInfo = $this->mongo->command(['buildinfo' => 1])[0];

        $this->assertArrayHasKey('version', $buildInfo);
        $this->assertRegExp('#^[0-9]+\.[0-9]+\.[0-9]+$#', $buildInfo['version']);
    }

    public function testGetCollectionNames() {
        $collectionNames = $this->mongo->getCollectionNames();

        $this->assertCount(1, $collectionNames);
        $this->assertContains(self::THE_BASS, $collectionNames);
    }

    public function test__toString(){
        $this->assertEquals(self::MONGO_DB_NAME, $this->mongo->__toString());
    }
}