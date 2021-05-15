<?php
namespace Zeedhi\Framework\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

class DateTimeType extends \Doctrine\DBAL\Types\DateTimeType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof DateTime) {
            return $value;
        }

        $format = $platform->getDateTimeFormatString();
        $val = DateTime::createFromFormat($format, $value);

        if ( ! $val) {
            if ($val = date_create($value)) {
                $val = DateTime::createFromFormat($format, $val->format($format));
            }
        }

        if ( ! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $format);
        }
        return $val;
    }
}
