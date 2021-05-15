<?php
namespace Zeedhi\Framework\Bundle\EventListeners;

use Zeedhi\Framework\Events\OnException\Listener;
use Zeedhi\Framework\Log\AbstractLogger;

class OnException extends Listener{

    /** @var AbstractLogger */
    protected $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function onException(\Exception $e) {
        $message = "Uncaught exception {exceptionClass} with {message} was triggered in file {fileName} at line {line}";
        $this->logger->warning($message, array(
            "exceptionClass" => get_class($e),
            "message"        => $e->getMessage(),
            "fileName"       => $e->getFile(),
            "line"           => $e->getLine()
        ));
    }
}