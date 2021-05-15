<?php
namespace Zeedhi\Framework\ErrorHandler;

use Zeedhi\Framework\DTO\Response;

class ErrorToException implements ErrorHandler{

    /** @var int */
    protected $errorCodesSupported;

    public function __construct($errorCodesSupported = null) {
        $this->errorCodesSupported = $errorCodesSupported !== null ? $errorCodesSupported : E_ALL | E_STRICT;
    }

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
     * @throws ErrorException If Error was handled convert it to exception.
     *
     * @return bool                True if error has handled, false otherwise.
     */
    public function handle(Response $response, $errno, $errstr, $errfile, $errline, $errcontext) {
        $message = "PHP Error occurred '".$errstr."' at file ".$errfile." in line ".$errline.".";
        throw new ErrorException(htmlspecialchars_decode($message), 0, $errno, $errfile, $errline);
    }

    /**
     * The 'bitwise' error codes handled by this.
     *
     * @return int
     */
    public function getErrorCode() {
        return $this->errorCodesSupported;
    }
}