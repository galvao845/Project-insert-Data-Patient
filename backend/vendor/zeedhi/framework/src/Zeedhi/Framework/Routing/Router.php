<?php
namespace Zeedhi\Framework\Routing;
use Zeedhi\Framework\DTO\Request;

/**
 * Class Router
 *
 * Responsible for find appropriated controller for determined route.
 *
 * @package Zeedhi\Framework\Routing
 */
class Router {

    /** @const string */
    const METHOD_DELETE = 'DELETE';
    /** @const string */
    const METHOD_POST = 'POST';
    /** @const string */
    const METHOD_GET = 'GET';
    /** @const string */
    const METHOD_PUT = 'PUT';

    /** @var \Zeedhi\Framework\Routing\Route[] */
    protected $post = array();
    /** @var \Zeedhi\Framework\Routing\Route[] */
    protected $get = array();
    /** @var \Zeedhi\Framework\Routing\Route[] */
    protected $put = array();
    /** @var \Zeedhi\Framework\Routing\Route[] */
    protected $delete = array();
    /** @var Parser */
    protected $parser;
    /** @var boolean */
    protected $routesRead = false;

    /**
     * @param \Zeedhi\Framework\Routing\Parser $parser
     */
    public function setParser(Parser $parser) {
        $this->parser = $parser;
    }

    public function readRoutes() {
        if($this->routesRead) return;
        $this->parser->parseFile($this);
        $this->routesRead = true;
    }

    /**
     * Method that resolves the received URI.
     * Returns an array with the controller name and the method name
     *
     * @param Request $request Contains the method and url called.
     *
     * @throws Exception Route does not exist.
     *
     * @return array Where first position determine controller name, in DI Container, and second position his method.
     */
    public function resolveRoute(Request $request) {
        $routes = $this->getRoutesForRequestMethod($request);
        foreach ($routes as $route) {
            if ($route->matchRequest($request)) {
                return array($route->getController(), $route->getControllerMethod());
            }
        }

        throw Exception::routeDoesNotExist($request->getRoutePath());
    }

    /**
     * Function that add a new POST route
     *
     * @param $route
     */
    public function post($route) {
        $this->post[] = $route;
    }

    /**
     * Function that add a new GET route
     *
     * @param $route
     */
    public function get($route) {
        $this->get[] = $route;
    }

    /**
     * Function that add a new PUT route
     *
     * @param $route
     */
    public function put($route) {
        $this->put[] = $route;
    }

    /**
     * Function that add a new DELETE route
     *
     * @param $route
     */
    public function delete($route) {
        $this->delete[] = $route;
    }

    /**
     * Function that add a new route with ANY HTTP method
     *
     * @param $route
     */
    public function any($route) {
        $this->post($route);
        $this->put($route);
        $this->delete($route);
        $this->get($route);
    }

    /**
     * @param Request $request
     * @return Route[]
     * @throws Exception
     */
    protected function getRoutesForRequestMethod(Request $request) {
        $method = $request->getMethod();
        /** @var $routes \Zeedhi\Framework\Routing\Route[] */
        switch ($method) {
            case self::METHOD_POST:
                $routes = $this->post;
                break;
            case self::METHOD_GET:
                $routes = $this->get;
                break;
            case self::METHOD_PUT:
                $routes = $this->put;
                break;
            case self::METHOD_DELETE:
                $routes = $this->delete;
                break;
            default:
                throw Exception::invalidMethod($method);
                break;
        }
        return $routes;
    }
}