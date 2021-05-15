<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ExceptionHandler\ExceptionHandler;

class ExceptionHandlerImpl implements ExceptionHandler{

    public function handleException(\Exception $exception, Response $response) {
        $response->setError(new Response\Error($exception->getMessage(), $exception->getCode()));
    }

    public function getHandledExceptionClasses() {
        return array("\\Exception");
    }
}