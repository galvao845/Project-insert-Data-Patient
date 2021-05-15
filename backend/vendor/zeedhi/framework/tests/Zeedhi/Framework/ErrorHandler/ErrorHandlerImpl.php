<?php
namespace tests\Zeedhi\Framework\ErrorHandler;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ErrorHandler\ErrorHandler;

class ErrorHandlerImpl implements ErrorHandler{

    /**
     * This function will get the user's handlers and listeners errors and use them to treat
     * the error that occurred.
     *
     * @param Response $response Response to be sent to client.
     * @param integer $errno Contains the level of the error raised.
     * @param string $errstr Contains the error message.
     * @param string $errfile Which contains the filename that the error was raised in.
     * @param integer $errline Which contains the line number the error was raised at.
     * @param array $errcontext Which is an array that points to the active symbol table at the point the error occurred.
     *                             In other words, errcontext will contain an array of every variable that existed in the
     *                             scope the error was triggered in. User error handler must not modify error context.
     *
     * @return bool                True if error has handled, false otherwise.
     */
    public function handle(Response $response, $errno, $errstr, $errfile, $errline, $errcontext) {
        $response->setError(new Response\Error("Fatal error({$errno}): {$errstr}. At file {$errfile}({$errline}).", $errno));
        return true;
    }

    /**
     * The 'bitwise' error codes handled by this.
     *
     * @return int
     */
    public function getErrorCode() {
        return E_ALL;
    }
} 