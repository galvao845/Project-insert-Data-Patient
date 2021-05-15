<?php
namespace tests\Zeedhi\Framework\Controller;

use Zeedhi\Framework\Controller\Simple;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;

class SimpleImpl extends Simple{

    public function successMethod(Request $request, Response $response) {
        $response->addMessage(new Response\Message("Method success fully called."));
    }
} 