<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine\Events\BeforeFind;

use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\Events\AbstractEvent;

class Event extends AbstractEvent{

    protected function validateArgs(array $args) {
        return isset($args[0]) && $args[0] instanceof FilterCriteria;
    }
} 