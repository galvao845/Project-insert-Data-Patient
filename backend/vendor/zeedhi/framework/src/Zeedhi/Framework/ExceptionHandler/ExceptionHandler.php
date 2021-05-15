<?php
namespace Zeedhi\Framework\ExceptionHandler;

use Zeedhi\Framework\DTO\Response;

/**
 * Interface ExceptionHandler
 *
 * @package Zeedhi\Framework\ExceptionHandler
 */
interface ExceptionHandler {

    /**
     * Handle exception and populate response.
     *
     * @param \Exception $exception The exception to be handled.
     * @param Response   $response  The response to be sent to client.
     *
     * @return void
     */
    public function handleException(\Exception $exception, Response $response);

    /**
     * Return a list of exceptions name that are handled by this.
     *
     * @return string[]
     */
    public function getHandledExceptionClasses();
}