<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\BeforeDeleteRow;

use Zeedhi\Framework\Events\AbstractEvent;

class Event extends AbstractEvent{

    protected function validateArgs(array $args) {
        return isset($args[0]) && is_array($args[0])
            || isset($args[1]) && is_numeric($args[1]);
    }
} 