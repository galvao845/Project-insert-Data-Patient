<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo\Types;

use InvalidArgumentException;

class DateType extends Type {
    
    private static function craftDateTime($seconds, $microseconds = 0) {
        $datetime = new \DateTime('now', new \DateTimeZone("UTC"));
        $datetime->setTimestamp($seconds);
        if ($microseconds > 0) {
            $datetime = \DateTime::createFromFormat('Y-m-d H:i:s.u', $datetime->format('Y-m-d H:i:s') . '.' . $microseconds, self::getUTC());
        }
        return $datetime;
    }

    /**
     * Converts a value to a DateTime.
     * Supports microseconds
     *
     * @throws InvalidArgumentException if $value is invalid
     *
     * @param  mixed $value \DateTime|\MongoDB\BSON\UTCDateTime|int|float
     *
     * @return \DateTime
     */
    public static function getDateTime($value) {
        $datetime = false;
        $exception = null;
        if ($value instanceof \DateTime) {
            $datetime = $value;
        } elseif ($value instanceof \MongoDB\BSON\UTCDateTime) {
            $datetime = $value->toDateTime();
        } elseif (is_numeric($value)) {
            $seconds = $value;
            $microseconds = 0;
            $matches = preg_split('/[\,\.]/', $value);
            if (isset($matches[1])) {
                list($seconds, $microseconds) = $matches;
                $microseconds = (int)str_pad((int)$microseconds, 6, '0'); // ensure microseconds
            }
            $datetime = self::craftDateTime($seconds, $microseconds);
        } elseif (is_string($value)) {
            try {
                $datetime = new \DateTime($value, self::getUTC());
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        if ($datetime === false) {
            throw new \InvalidArgumentException(sprintf('Could not convert %s to a date value', is_scalar($value) ? '"' . $value . '"' : gettype($value)), 0, $exception);
        }
        return $datetime;
    }

    /**
     * @return \DateTimeZone
     */
    public static function getUTC() {
        return new \DateTimeZone("UTC");
    }

    public function convertToDatabaseValue($value) {
        if ($value === null || $value instanceof \MongoDB\BSON\UTCDateTime) {
            return $value;
        }
        $datetime = self::getDateTime($value);
        return new \MongoDB\BSON\UTCDateTime($datetime);
    }

    public function convertToPHPValue($value) {
        if ($value === null) {
            return null;
        }
        return self::getDateTime($value);
    }


}