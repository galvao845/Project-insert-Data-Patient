<?php
namespace Zeedhi\Framework\Events\OnException;

use Zeedhi\Framework\Events\AbstractEvent;

class Event extends AbstractEvent {

    protected function validateArgs(array $args) {
        return $args[0] instanceof \Exception;
    }
}