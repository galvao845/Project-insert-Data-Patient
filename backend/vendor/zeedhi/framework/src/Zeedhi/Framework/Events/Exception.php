<?php
namespace Zeedhi\Framework\Events;

class Exception extends \Exception{

    public static function invalidTriggerArguments($eventClassName) {
        return new static("Can't trigger {$eventClassName}, because was given wrong parameters.");
    }
}