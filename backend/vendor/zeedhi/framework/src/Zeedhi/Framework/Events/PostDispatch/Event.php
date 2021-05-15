<?php
namespace Zeedhi\Framework\Events\PostDispatch;

use Zeedhi\Framework\Events\AbstractEvent;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;

class Event extends AbstractEvent {

    protected function validateArgs(array $args) {
        return isset($args[0]) && $args[0] instanceof Request
            || isset($args[0]) && $args[1] instanceof Response;
    }
}