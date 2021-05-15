<?php
namespace tests\Zeedhi\Framework\ORM;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;

use Zeedhi\Framework\ORM\DateTime;

class DateTimeTypeTest extends \PHPUnit\Framework\TestCase {

    /** @var MockPlatform */
    protected $_platform;
    /** @var \Doctrine\DBAL\Types\Type */
    protected $_type;

    protected function setUp() {
        \Doctrine\DBAL\Types\Type::overrideType('datetime', '\Zeedhi\Framework\ORM\DateTimeType');
        $this->_platform = new MockPlatform();
        $this->_type = Type::getType('datetime');
    }

    public function testDateTimeConvertsToDatabaseValue()
    {
        $date = new DateTime('1985-09-01 10:10:10');

        $expected = $date->format($this->_platform->getDateTimeTzFormatString());
        $actual = $this->_type->convertToDatabaseValue($date, $this->_platform);

        $this->assertEquals($expected, $actual);
    }

    public function testDateTimeConvertsToPHPValue()
    {
        // Birthday of jwage and also birthday of Doctrine. Send him a present ;)
        $date = $this->_type->convertToPHPValue('1985-09-01 00:00:00', $this->_platform);
        $this->assertInstanceOf('DateTime', $date);
        $this->assertEquals('1985-09-01 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testInvalidDateTimeFormatConversion()
    {
        $this->expectException(ConversionException::class);
        $this->_type->convertToPHPValue('abcdefg', $this->_platform);
    }

    public function testNullConversion()
    {
        $this->assertNull($this->_type->convertToPHPValue(null, $this->_platform));
    }

    public function testConvertDateTimeToPHPValue()
    {
        $date = new DateTime("now");
        $this->assertSame($date, $this->_type->convertToPHPValue($date, $this->_platform));
    }

    public function testConvertsNonMatchingFormatToPhpValueWithParser()
    {
        $date = '1985/09/01 10:10:10.12345';

        $actual = $this->_type->convertToPHPValue($date, $this->_platform);

        $this->assertEquals('1985-09-01 10:10:10', $actual->format('Y-m-d H:i:s'));
    }
}
