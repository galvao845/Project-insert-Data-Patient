<?php
namespace Zeedhi\Framework\ORM;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class IntegerType extends \Doctrine\DBAL\Types\IntegerType {

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        return $value === null ? null : (int)$value;
    }
}