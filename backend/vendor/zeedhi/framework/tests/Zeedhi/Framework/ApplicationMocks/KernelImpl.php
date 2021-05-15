<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\Kernel;
use Zeedhi\Framework\Routing\Router;

class KernelImpl implements Kernel {

    CONST USER_ID = "bhlb9n2oq8lac3di";
    protected $sentResponses = array();
    protected $requestPath = "/blog";

    /**
     * @param string $requestPath
     */
    public function setRequestPath($requestPath) {
        $this->requestPath = $requestPath;
    }

    /**
     * Consulting global vars e create a DTO\Request object.
     *
     * @return DTO\Request
     */
    public function getRequest() {
        return new DTO\Request(Router::METHOD_POST, $this->requestPath, self::USER_ID);
    }

    /**
     * Send a response to interface.
     *
     * @param DTO\Response $response
     *
     * @return mixed
     */
    public function sendResponse(DTO\Response $response) {
        $this->sentResponses[] = $response;
    }

    /**
     * @return DTO\Response[]
     */
    public function getSentResponses() {
        return $this->sentResponses;
    }
} 