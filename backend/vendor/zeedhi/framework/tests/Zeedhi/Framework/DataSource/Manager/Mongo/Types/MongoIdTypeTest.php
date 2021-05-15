<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo\Types;

use Zeedhi\Framework\DataSource\Manager\Mongo\Types\MongoIdType;

class MongoIdTypeTest extends \PHPUnit\Framework\TestCase {

    const RANDOM_MONGO_ID = '56afcea9154bb4a01400002a';

    /** @var IdType */
    protected $type;

    protected function setUp() {
        $extensionLoaded = extension_loaded('mongo');
        if (!$extensionLoaded) {
            $this->markTestSkipped('The tests require mongo extension');
        }

        parent::setUp();
        $this->type = new MongoIdType();
    }

    public function testConvertToDatabaseValue() {
        $expected = new \MongoDB\BSON\ObjectId(self::RANDOM_MONGO_ID);
        $databaseValue = $this->type->convertToDatabaseValue(self::RANDOM_MONGO_ID);
        $this->assertEquals($expected, $databaseValue);
    }

    public function testConvertToPHPValue() {
        $expected = self::RANDOM_MONGO_ID;
        $mongoId = new \MongoDB\BSON\ObjectId(self::RANDOM_MONGO_ID);
        $phpValue = $this->type->convertToPHPValue($mongoId);
        $this->assertSame($expected, $phpValue);
    }

    public function testConvertToDatabaseValueNull() {
        $databaseValue = $this->type->convertToDatabaseValue(null);
        $this->assertNull($databaseValue);
    }

    public function testConvertToDatabaseValueInvalidId() {
        $databaseValue = $this->type->convertToDatabaseValue('123');
        $this->assertNotEquals("123", $databaseValue);
        $this->assertInstanceOf('MongoDB\BSON\ObjectId', $databaseValue);
    }
}