<?php
namespace Zeedhi\Framework;

interface Kernel {

    /**
     * Consulting global vars e create a DTO\Request object.
     *
     * @return DTO\Request
     */
    public function getRequest();

    /**
     * Send a response to interface.
     *
     * @param DTO\Response $response
     */
    public function sendResponse(DTO\Response $response);
}