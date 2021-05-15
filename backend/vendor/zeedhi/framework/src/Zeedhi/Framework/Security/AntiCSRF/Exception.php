<?php
namespace Zeedhi\Framework\Security\AntiCSRF;

class Exception extends \Exception {

    const COOKIE_NOT_FOUND_REQUEST = 1;
    const TOKEN_NOT_FOUND_REQUEST = 2;
    const INVALID_TOKEN = 3;
    const INVALID_ACCESS = 4;

    public static function cookieNotFoundInRequest(){
        return new static(
            "Cookie not found in request header.",
            static::COOKIE_NOT_FOUND_REQUEST
        );
    }

    public static function tokenNotFoundInRequest(){
        return new static(
            "Token not found in request header.",
            static::TOKEN_NOT_FOUND_REQUEST
        );
    }

    public static function invalidToken(){
        return new static(
            "Invalid token/cookie.",
            static::INVALID_TOKEN
        );
    }

    public static function invalidAccess() {
        return new static("Invalid access.", static::INVALID_ACCESS);
    }
}
