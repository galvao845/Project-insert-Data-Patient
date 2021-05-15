<?php
namespace Zeedhi\Framework\Events\OnException;

abstract class Listener implements \Zeedhi\Framework\Events\Listener {

    abstract public function onException(\Exception $e);

    public function notify(array $args) {
        $this->onException($args[0]);
    }
}