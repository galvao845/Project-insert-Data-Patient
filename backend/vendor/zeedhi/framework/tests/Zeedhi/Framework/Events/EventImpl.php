<?php
namespace tests\Zeedhi\Framework\Events;

use Zeedhi\Framework\Events\AbstractEvent;
use Zeedhi\Framework\Events\Listener;

class EventImpl extends AbstractEvent{

    protected function validateArgs(array $args) {
        return is_string($args[0]);
    }

    /**
     * @return Listener[]
     */
    public function getListeners() {
        return $this->listeners;
    }
}