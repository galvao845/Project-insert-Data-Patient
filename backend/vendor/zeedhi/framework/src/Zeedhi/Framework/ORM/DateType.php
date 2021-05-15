<?php
namespace Zeedhi\Framework\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

class DateType extends \Doctrine\DBAL\Types\DateType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof DateTime) {
            return $value;
        }

        $format = $platform->getDateFormatString();
        $val = DateTime::createFromFormat('!'.$format, $value);

        if ( ! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $format);
        }
        return $val;
    }
}
