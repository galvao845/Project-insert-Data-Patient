<?php
namespace tests\Zeedhi\Framework\Events;

use Zeedhi\Framework\Events\Listener;

class ListenerImpl implements Listener{

    protected $notified = false;

    public function notify(array $args) {
        $this->notified = true;
    }

    /**
     * @return boolean
     */
    public function isNotified() {
        return $this->notified;
    }
} 