<?php
namespace Zeedhi\Framework\Remote;

use Zeedhi\Framework\DTO\Response\Error;

class ServerException extends Exception {

    const RESPONSE_ERROR = 1;

    protected $responseBody;

    public function __construct($message, $code, $responseBody) {
        parent::__construct($message, $code);
        $this->responseBody = $responseBody;
    }

    public function getResponseBody() {
        return $this->responseBody;
    }

    public static function badFormattedResponse($responseBody) {
        return new static('Error parsing response', self::RESPONSE_ERROR, $responseBody);
    }

}