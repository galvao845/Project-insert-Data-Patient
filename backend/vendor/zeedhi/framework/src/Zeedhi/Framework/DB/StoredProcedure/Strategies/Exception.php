<?php
namespace Zeedhi\Framework\DB\StoredProcedure\Strategies;

class Exception extends \Exception {

    public static function unsupportedDriver($driverName) {
        return new static("Unsupported driver {$driverName}");
    }

}