<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo\Types;

use Zeedhi\Framework\DataSource\Manager\Mongo\Types\CollectionType;

class CollectionTypeTest extends \PHPUnit\Framework\TestCase {

    public function testConvertToDatabaseValue() {
        $collectionType = new CollectionType();
        $value = '123';
        $databaseValue = $collectionType->convertToDatabaseValue($value);
        $this->assertSame($value, $databaseValue);
    }

    public function testConvertToPHPValue() {
        $collectionType = new CollectionType();
        $value = '123';
        $phpValue = $collectionType->convertToPHPValue($value);
        $this->assertSame($value, $phpValue);
    }

    public function testGetInvalidType() {
        $this->expectException('\InvalidArgumentException');
        CollectionType::getType('invalid');
    }
}