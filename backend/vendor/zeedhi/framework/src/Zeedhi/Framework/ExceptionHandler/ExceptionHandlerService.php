<?php
namespace Zeedhi\Framework\ExceptionHandler;

use Zeedhi\Framework\DTO\Response\Error;
use Zeedhi\Framework\HTTP\Response;

/**
 * Class ExceptionHandlerService
 *
 * ExceptionHandler converts an exception to a Response object.
 *
 * @package Zeedhi\Framework\ExceptionHandler
 */
class ExceptionHandlerService
{
    /** @var ExceptionHandler[] $handlers */
    protected $handlers = array();

    /**
     * Add a ExceptionHandler to service.
     *
     * @param ExceptionHandler $handler The new handler.
     *
     * @return void
     */
    public function addHandler(ExceptionHandler $handler) {
        $this->handlers[] = $handler;
    }

    /**
     * Handler a exception and populate response.
     *
     * @param \Exception                     $exception Exception to be handled.
     * @param \Zeedhi\Framework\DTO\Response $response  Response to be sent to client.
     *
     * @return void
     */
    public function handle(\Exception $exception, \Zeedhi\Framework\DTO\Response $response) {
        foreach($this->handlers as $handler) {
            foreach($handler->getHandledExceptionClasses() as $handledException) {
                if($exception instanceof $handledException) {
                    $handler->handleException($exception, $response);
                    return;
                }
            }
        }

        $this->defaultHandle($exception, $response);
    }

    /**
     * Sends a response for the given Exception,
     * if there isn't to handle the specified exception.
     *
     * @param \Exception $exception
     * @param \Zeedhi\Framework\DTO\Response $response
     *
     * @return void
     */
    private function defaultHandle(\Exception $exception, \Zeedhi\Framework\DTO\Response $response) {
        $exceptionClass = get_class($exception);
        $response->setStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setCriticalError(new Error($exceptionClass.": ".$exception->getMessage(), $exception->getCode(), $exception->getTraceAsString(), $exception->getPrevious()));
    }
}