<?php
namespace Zeedhi\Framework\Remote;

use Zeedhi\Framework\DTO\Response\Error;

class Exception extends \Exception {

    public static function remoteServerError(Error $error) {
        return new static("Error in remote server: ".$error->getMessage(), $error->getErrorCode());
    }

    public static function curlError($errorMessage, $errorCode) {
        return new static('Error ocurred on curl request ['.$errorCode.']: '.$errorMessage);
    }

}