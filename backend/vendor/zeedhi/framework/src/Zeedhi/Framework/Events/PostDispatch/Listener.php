<?php
namespace Zeedhi\Framework\Events\PostDispatch;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;

abstract class Listener implements \Zeedhi\Framework\Events\Listener{

    abstract public function postDispatch(Request $request, Response $response);

    public function notify(array $args) {
        $this->postDispatch($args[0], $args[1]);
    }
} 