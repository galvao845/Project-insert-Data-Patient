<?php
namespace Zeedhi\Framework\Log;

use Psr\Log\InvalidArgumentException;

class Exception extends InvalidArgumentException {

    public static function invalidLogLevel($level) {
        return new static("Invalid log level {$level}.");
    }
} 