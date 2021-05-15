<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\Controller\Simple;

class ErrorController extends Simple{

    public function error($response, $request) {
        trigger_error("Error controller");
    }
}