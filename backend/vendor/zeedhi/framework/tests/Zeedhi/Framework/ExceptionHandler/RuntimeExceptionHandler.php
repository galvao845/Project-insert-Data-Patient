<?php
namespace tests\Zeedhi\Framework\ExceptionHandler;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ExceptionHandler\ExceptionHandler;

class RuntimeExceptionHandler implements ExceptionHandler{

    /**
     * Handle exception and populate response.
     *
     * @param \Exception $exception The exception to be handled.
     * @param Response   $response  The response to be sent to client.
     *
     * @return void
     */
    public function handleException(\Exception $exception, Response $response) {
        $response->setError(new Response\Error("[RuntimeException]: ".$exception->getMessage(), $exception->getCode(), $exception->getTraceAsString()));
        $response->setStatus(Response::STATUS_ERROR);
    }

    /**
     * Return a list of exceptions name that are handled by this.
     *
     * @return string[]
     */
    public function getHandledExceptionClasses() {
        return array('RuntimeException');
    }


}