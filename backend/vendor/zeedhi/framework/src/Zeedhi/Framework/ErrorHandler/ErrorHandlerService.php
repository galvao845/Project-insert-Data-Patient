<?php
namespace Zeedhi\Framework\ErrorHandler;

use Zeedhi\Framework\DTO\Response;

class ErrorHandlerService {

    /** @var ErrorHandler[] */
    protected $handlers = array();
    /** @var Response */
    protected $response;
    /** @var int */
    protected $errorLevel = E_ALL | E_STRICT;

    /**
     * ErrorHandlerService constructor.
     * @param int $errorLevel
     */
    public function __construct($errorLevel = null) {
        if (is_numeric($errorLevel)) {
            $this->errorLevel = $errorLevel;
        }
    }

    /**
     * This function will get the user's handlers and listeners errors and use them to treat
     * the error that occurred.
     *
     * @param integer $errno      Contains the level of the error raised.
     * @param string  $errstr     Contains the error message.
     * @param string  $errfile    Which contains the filename that the error was raised in.
     * @param integer $errline    Which contains the line number the error was raised at.
     * @param array   $errcontext Which is an array that points to the active symbol table at the point the error occurred.
     *                            In other words, errcontext will contain an array of every variable that existed in the
     *                            scope the error was triggered in. User error handler must not modify error context.
     *
     * @return bool
     */
    public function handleError($errno, $errstr, $errfile, $errline, $errcontext) {
        if ($this->isNotSuppressedError()) {
            foreach($this->handlers as $handler) {
                if ($handler->getErrorCode() & $errno) {
                    $handled = $handler->handle($this->response, $errno, $errstr, $errfile, $errline, $errcontext);
                    if ($handled) return true;
                }
            }
        }

        return false;
    }

    /**
     * This handle fatal errors that stop the script execution.
     *
     * @return void
     */
    public function handleShutdown() {
        if ($error = error_get_last()) {
            switch($error['type']) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_CORE_WARNING:
                case E_USER_ERROR:
                    //These cases verify that shutdown was caused by a error.
                    $this->handleError($error['type'], $error['message'], $error['file'], $error['line'], null);
                    break;
            }
        }
    }

    /**
     * Add a error handler to service.
     *
     * @param ErrorHandler $handler The handler.
     *
     * @return void
     */
    public function addHandler(ErrorHandler $handler) {
        $this->handlers[] = $handler;
    }

    /**
     * Set response to be sent to client.
     *
     * @param \Zeedhi\Framework\DTO\Response $response The response.
     *
     * @return void
     */
    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * Register this class as error handler and shutdown.
     *
     * @return void
     */
    public function register() {
        set_error_handler(array($this, 'handleError'), $this->errorLevel);
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * Verify if the error happened in a expression without the @ operator.
     *
     * @return bool
     */
    protected function isNotSuppressedError() {
        return error_reporting() !== 0;
    }
}