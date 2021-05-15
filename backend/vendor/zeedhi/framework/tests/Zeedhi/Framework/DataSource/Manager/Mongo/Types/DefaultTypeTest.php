<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo\Types;

use InvalidArgumentException;

use Zeedhi\Framework\DataSource\Manager\Mongo\Types\DefaultType;

class DefaultTypeTest extends \PHPUnit\Framework\TestCase {

    public function testConvertToDatabaseValue() {
        $defaultType = new DefaultType();
        $value = '123';
        $databaseValue = $defaultType->convertToDatabaseValue($value);
        $this->assertSame($value, $databaseValue);
    }

    public function testConvertToPHPValue() {
        $defaultType = new DefaultType();
        $value = '123';
        $phpValue = $defaultType->convertToPHPValue($value);
        $this->assertSame($value, $phpValue);
    }

    public function testGetInvalidType() {
        $this->expectException(InvalidArgumentException::class);
        DefaultType::getType('invalid');
    }
}