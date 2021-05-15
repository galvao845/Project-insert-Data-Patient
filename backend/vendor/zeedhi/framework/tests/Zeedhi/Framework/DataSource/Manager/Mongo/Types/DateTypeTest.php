<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo\Types;

use Zeedhi\Framework\DataSource\Manager\Mongo\Types\DateType;

class DateTypeTest extends \PHPUnit\Framework\TestCase {

    protected function setUp() {
        $extensionLoaded = extension_loaded('mongodb');

        if (!$extensionLoaded) {
            $this->markTestSkipped('The tests require mongo extension');
        }

        parent::setUp();
    }

    public function testGetDateTimePassingDateTime() {
        $dateTime = new \DateTime();
        $newDateTime = DateType::getDateTime($dateTime);

        $this->assertEquals($dateTime, $newDateTime);
    }

    public function testGetDateTimePassingMongoDate() {
        $dateTime = new \MongoDB\BSON\UTCDateTime();
        $newDateTime = DateType::getDateTime($dateTime);
        $expected = new \DateTime('now', new \DateTimeZone("UTC"));

        $this->assertEquals($expected->format('Y-m-d H:i:s'), $newDateTime->format('Y-m-d H:i:s'));
    }

    public function testGetDateTimePassingNumber() {
        $expected = new \DateTime('now', DateType::getUTC());

        $dateTime = time() + 0.852;
        $newDateTime = DateType::getDateTime($dateTime);

        $this->assertSame('852000', $newDateTime->format('u'));
        $this->assertEquals($expected->format('d/m/Y H:i:s'), $newDateTime->format('d/m/Y H:i:s'));
    }

    public function testGetDateTimePassingString() {
        $dateTime = 'now';
        $newDateTime = DateType::getDateTime($dateTime);

        $expected = new \DateTime('now', DateType::getUTC());

        $this->assertEquals($expected->format("m-d-Y H:i:s"), $newDateTime->format("m-d-Y H:i:s"));
    }

    public function testGetDateTimePassingInvalidString() {
        $this->expectException('\InvalidArgumentException');
        DateType::getDateTime('invalid date');
    }

    public function testGetDateTimePassingFalse() {
        $this->expectException('\InvalidArgumentException');
        DateType::getDateTime(false);
    }

    public function testConvertToDatabaseValue() {
        $dateTimeType = new DateType();
        $dateTime = time();
        $newDateTime = $dateTimeType->convertToDatabaseValue($dateTime);

        $this->assertInstanceOf('\MongoDB\BSON\UTCDateTime', $newDateTime);
    }

    public function testConvertToDatabaseValuePassingNull() {
        $dateTimeType = new DateType();
        $dateTime = null;
        $newDateTime = $dateTimeType->convertToDatabaseValue($dateTime);
        $this->assertNull($newDateTime);
    }

    public function testConvertToDatabaseValuePassingMongoDate() {
        $dateTimeType = new DateType();
        $dateTime = new \MongoDB\BSON\UTCDateTime();
        $newDateTime = $dateTimeType->convertToDatabaseValue($dateTime);
        $this->assertEquals($dateTime, $newDateTime);
    }

    public function testConvertToPHPValue() {
        $dateTimeType = new DateType();
        $phpDateTime = new \DateTime();
        $newDateTime = $dateTimeType->convertToPHPValue(new \DateTime());
        $this->assertInstanceOf('DateTime', $newDateTime);
        $this->assertEquals($phpDateTime->format("m-d-Y H:i:s"), $newDateTime->format("m-d-Y H:i:s"));
    }

    public function testConvertToPHPValuePassingMongoDate() {
        $dateTimeType = new DateType();
        $newDateTime = $dateTimeType->convertToPHPValue(new \MongoDB\BSON\UTCDateTime());
        $this->assertInstanceOf('DateTime', $newDateTime);
        $expected = new \DateTime();
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $newDateTime->format('Y-m-d H:i:s'));
    }

    public function testConvertToPHPValuePassingNull() {
        $dateTimeType = new DateType();
        $newDateTime = $dateTimeType->convertToPHPValue(null);
        $this->assertNull($newDateTime);
    }

}