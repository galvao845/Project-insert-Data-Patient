<?php
namespace Zeedhi\Framework\Events\PreDispatch;

use Zeedhi\Framework\DTO\Request;

abstract class Listener implements \Zeedhi\Framework\Events\Listener{

    abstract public function preDispatch(Request $request);

    public function notify(array $args) {
        $this->preDispatch($args[0]);
    }
} 