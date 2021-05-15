<?php
namespace Zeedhi\Framework\Events\PreDispatch;

use Zeedhi\Framework\Events\AbstractEvent;
use Zeedhi\Framework\DTO\Request;

class Event extends AbstractEvent{

    protected function validateArgs(array $args) {
        return $args[0] instanceof Request;
    }
} 