<?php
namespace Zeedhi\Framework\Routing;

/**
 * Class Parser
 *
 * Read a route configuration and populate the router.
 *
 * @package Zeedhi\Framework\Routing
 */
abstract class Parser {
    /** @var array */
    protected $routesFile;

    public function __construct($routesFile) {
        $this->routesFile = (array)$routesFile;
    }

    /**
     * Method to be implemented to parse a route file that you've created
     * with any format to the format recognized by the framework
     *
     * @param Router $router
     *
     * @return mixed
     */
    abstract public function parseFile(Router $router);
} 