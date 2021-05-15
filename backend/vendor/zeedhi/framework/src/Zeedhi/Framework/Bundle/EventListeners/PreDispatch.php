<?php
namespace Zeedhi\Framework\Bundle\EventListeners;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Events\PreDispatch\Listener;
use Zeedhi\Framework\Log\AbstractLogger;

class PreDispatch extends Listener{

    /** @var AbstractLogger */
    protected $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function preDispatch(Request $request) {
        $message = "PreDispatch request content {requestContent}";
        $this->logger->debug($message, array(
            "requestContent" => var_export($request, true)
        ));
    }
} 