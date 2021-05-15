<?php
namespace Zeedhi\Framework\Bundle\EventListeners;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Events\PostDispatch\Listener;
use Zeedhi\Framework\Log\AbstractLogger;

class PostDispatch extends Listener{

    /** @var AbstractLogger */
    protected $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function postDispatch(Request $request, Response $response) {
        $message = "PreDispatch request content {requestContent} response content {responseContent}";
        $this->logger->debug($message, array(
            "requestContent"  => var_export($request, true),
            "responseContent" => var_export($response, true)
        ));
    }
}