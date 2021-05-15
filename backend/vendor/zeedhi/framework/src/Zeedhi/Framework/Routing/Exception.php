<?php
namespace Zeedhi\Framework\Routing;

/**
 * Class Exception
 *
 * Exception class for Routing package.
 *
 * @package Zeedhi\Framework\Routing
 */
class Exception extends \Exception{

    /**
     * Alert that a given route does not exist.
     *
     * @param string $uri The URI that doesn't match any route.
     *
     * @return Exception
     */
    public static function routeDoesNotExist($uri) {
        return new self("Route {$uri} does not exist.");
    }

    /**
     * Alert that given method does not exist.
     *
     * @param string $method The given method.
     *
     * @return Exception
     */
    public static function invalidMethod($method) {
        return new self("Invalid method {$method}.");
    }
} 