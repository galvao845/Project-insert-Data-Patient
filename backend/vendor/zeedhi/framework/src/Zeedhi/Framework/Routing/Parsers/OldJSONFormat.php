<?php
namespace Zeedhi\Framework\Routing\Parsers;

use Zeedhi\Framework\Routing\Parser;
use Zeedhi\Framework\Routing\Route;
use Zeedhi\Framework\Routing\Router;

/**
 * Class OldJSONFormat
 *
 * Class used to parse the old JSON routes file to the new format
 *
 * @package Zeedhi\Framework\Routing\Parsers
 */
class OldJSONFormat extends Parser {

    /**
     * @param Router $router
     *
     * @return void
     */
    public function parseFile(Router $router) {
        $methods = array(Router::METHOD_GET, Router::METHOD_POST, Router::METHOD_PUT, Router::METHOD_DELETE);
        $routes = array();
        foreach($this->routesFile as $routePath) {
            $routes = array_merge($routes, json_decode(file_get_contents($routePath), true));
        }
        foreach($routes as $routeUri => $routeAction) {
            list($controller, $controllerMethod) = explode("::", $routeAction);
            $router->any(new Route($methods, $routeUri, $controller, $controllerMethod));
        }
    }
} 