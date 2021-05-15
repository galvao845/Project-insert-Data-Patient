<?php
namespace Zeedhi\Framework\DTO\Response;
/**
 * Class Error
 *
 * Contain error that are returned in response
 *
 * @package Zeedhi\Framework\DTO\Response
 */
class Error {

    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $stackTrace;
    /**
     * @var int
     */
    private $errorCode;
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * Constructor
     *
     * @param string $message   The content of the error
     * @param int    $errorCode Number of error code
     * @param string $stackTrace Error call stack trace
     */
    function __construct($message, $errorCode, $stackTrace = null, \Exception $exception = null){
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->stackTrace = $stackTrace;
        $this->exception = $exception;
    }

    /**
     * Returns error.
     *
     * @return Error
     */
    public static function factoryFromException(\Exception $exception, $message, $errorCode){
        return new Error($message, $errorCode, $exception->getTraceAsString(), $exception);
    }

    /**
     * Returns exception.
     *
     * @return \Exception
     */
    public function getException(){
        return $this->exception;
    }

    /**
     * Returns error code.
     *
     * @return int
     */
    public function getErrorCode(){
        return $this->errorCode;
    }

    /**
     * Returns content the error.
     *
     * @return string
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * Returns the stack trace for the error
     *
     * @return string
     */
    public function getStackTrace(){
        return $this->stackTrace;
    }

}