<?php
/**
 * Created by PhpStorm.
 * User: icaroharry
 * Date: 29/07/14
 * Time: 09:30
 */
namespace Zeedhi\Framework\Routing\Parsers;

use Zeedhi\Framework\Routing\Parser;
use Zeedhi\Framework\Routing\Route;
use Zeedhi\Framework\Routing\Router;

class JSONFormat extends Parser {

    /**
     * Method to be implemented to parse a route file that you've created
     * with any format to the format recognized by the framework
     *
     * This parser works with the new JSON format defined for routes.json
     * This is a suggestion for a more complete definition of a route,
     * but the old JSON Format still working with the OldJSONFormat Parser class.
     * A complete route is defined by the parameters:
     *
     * String   "uri"
     * Array    "where" of { String "param" String "regex" }
     * String   "controller"
     * String   "controllerMethod"
     * Array of String "methods"
     *
     * @param Router $router
     *
     * @return mixed
     */
    public function parseFile(Router $router) {
        $routesJson = array();
        foreach($this->routesFile as $routePath) {
            $routesJson = array_merge(
                $routesJson,
                $this->getRoutesFromFile($routePath)
            );
        }
        foreach($routesJson as $route) {
            $newRoute = new Route(
                $route['methods'],
                $route['uri'],
                $route['controller'],
                $route['controllerMethod'],
                isset($route['requestType']) ? $route['requestType'] : null,
                isset($route['params']) ? $route['params'] : array()
            );
            foreach($route['methods'] as $method){
                $methodName = strtolower($method);
                $router->$methodName($newRoute);
            }
        }
    }

    /**
     * Read routes configuration from file
     *
     * @param string $routePath Routes configuration file path
     * @return array
     */
    protected function getRoutesFromFile($routePath) {
        $routes = json_decode(file_get_contents($routePath), true);

        if ($routes === null) {
            throw JSONFormatException::invalidJSON($routePath, json_last_error_msg());
        }

        return $routes;
    }

}