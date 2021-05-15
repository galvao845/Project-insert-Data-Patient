<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;

class ExceptionController{

    public function exception(Request $request, Response $response) {
        throw new \Exception("Exception!");
    }
} 