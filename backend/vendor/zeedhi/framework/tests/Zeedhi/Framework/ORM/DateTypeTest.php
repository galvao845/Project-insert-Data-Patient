<?php
namespace tests\Zeedhi\Framework\ORM;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;
use Zeedhi\Framework\ORM\DateTime;

class DateTypeTest extends \PHPUnit\Framework\TestCase {

    /** @var MockPlatform */
    protected $_platform;
    /** @var Type */
    protected $_type;
    protected $_tz;

    protected function setUp()
    {
        Type::overrideType('date', '\Zeedhi\Framework\ORM\DateType');
        $this->_platform = new MockPlatform();
        $this->_type = Type::getType('date');
        $this->_tz = date_default_timezone_get();
    }

    public function tearDown()
    {
        date_default_timezone_set($this->_tz);
    }

    public function testDateConvertsToDatabaseValue()
    {
        $this->assertTrue(
            is_string($this->_type->convertToDatabaseValue(new DateTime(), $this->_platform))
        );
    }

    public function testDateConvertsToPHPValue()
    {
        // Birthday of jwage and also birthday of Doctrine. Send him a present ;)
        $this->assertTrue(
            $this->_type->convertToPHPValue('1985-09-01', $this->_platform)
            instanceof \DateTime
        );
    }

    public function testDateResetsNonDatePartsToZeroUnixTimeValues()
    {
        $date = $this->_type->convertToPHPValue('1985-09-01', $this->_platform);

        $this->assertEquals('00:00:00', $date->format('H:i:s'));
    }

    public function testDateRests_SummerTimeAffection()
    {
        date_default_timezone_set('Europe/Berlin');

        $date = $this->_type->convertToPHPValue('2009-08-01', $this->_platform);
        $this->assertEquals('00:00:00', $date->format('H:i:s'));
        $this->assertEquals('2009-08-01', $date->format('Y-m-d'));

        $date = $this->_type->convertToPHPValue('2009-11-01', $this->_platform);
        $this->assertEquals('00:00:00', $date->format('H:i:s'));
        $this->assertEquals('2009-11-01', $date->format('Y-m-d'));
    }

    public function testInvalidDateFormatConversion()
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

}