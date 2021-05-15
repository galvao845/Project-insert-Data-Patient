<?php
namespace Zeedhi\Framework\Events;

abstract class AbstractEvent {

    /** @var Listener[] */
    protected $listeners = array();

    public function addListener(Listener $listener) {
        $this->listeners[] = $listener;
    }

    public function removeListener(Listener $listener) {
        if (is_numeric($key = array_search($listener, $this->listeners))) {
            unset($this->listeners[$key]);
        }

        return $key;
    }

    public function trigger(array $args) {
        if ($this->validateArgs($args)) {
            foreach ($this->listeners as $listener) {
                $listener->notify($args);
            }
        } else {
            throw Exception::invalidTriggerArguments(get_class($this));
        }
    }

    abstract protected function validateArgs(array $args);
}