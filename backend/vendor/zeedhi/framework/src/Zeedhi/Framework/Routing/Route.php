<?php
namespace Zeedhi\Framework\Routing;

use Zeedhi\Framework\DTO\Request;

class Route {

    private static $requestClassByType = array(
        Request::TYPE_DATA_SET  => '\Zeedhi\Framework\DTO\Request\DataSet',
        Request::TYPE_FILTER    => '\Zeedhi\Framework\DTO\Request\Filter',
        Request::TYPE_ROW       => '\Zeedhi\Framework\DTO\Request\Row',
        Request::TYPE_EMPTY     => '\Zeedhi\Framework\DTO\Request'
    );

    /** @var string[] List of method supported by route. */
    protected $methods;

    /** @var string The uri matched by route. */
    protected $uri;

    /** @var string The controller name, normally in DI Container. */
    protected $controller;

    /** @var string The method name to be called in controller. */
    protected $controllerMethod;
    /** @var string Class name of supported request. */
    protected $supportedRequest;

    /** @var array The field (key) and the condition (value) for the route. */
    protected $parameters = array();


    /**
     * Construct...
     *
     * @param string[] $methods
     * @param string   $uri
     * @param string   $controller
     * @param string   $controllerMethod
     * @param string   $requestType
     * @param array    $parameters
     */
    public function __construct($methods, $uri, $controller, $controllerMethod, $requestType = null, $parameters = array()) {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->controller = $controller;
        $this->controllerMethod = $controllerMethod;
        if ($requestType !== null && isset(self::$requestClassByType[$requestType])) {
            $this->supportedRequest = self::$requestClassByType[$requestType];
        } else {
            $this->supportedRequest = self::$requestClassByType[Request::TYPE_EMPTY];
        }
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getControllerMethod() {
        return $this->controllerMethod;
    }

    /**
     * @return array
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getParameters() {
        return $this->parameters;
    }


    /**
     * Return true if route match given uri.
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function match(Request $request) {
        $uriToMatch = str_replace(array('{', '}'), '#', $this->uri);
        $uriToMatch = '/'.preg_quote($uriToMatch, '/').'/';
        foreach ($this->parameters as $parameter) {
            if (is_array($parameter)) {
                $parameterName = $parameter['name'];
                $regex = '('.$parameter['regex'].')';
            } else {
                $parameterName = $parameter;
                $regex = '(.*)';
            }
            $uriToMatch = str_replace('#' . $parameterName . '#', $regex, $uriToMatch);
        }

        $routePath = $request->getRoutePath();
        if ($matched = preg_match($uriToMatch, $routePath, $matches)) {
            foreach($this->parameters as $key => $parameter) {
                $parameterName = is_array($parameter) ? $parameter['name'] : $parameter;
                $match = $matches[$key+1];
                $request->setParameter($parameterName, $match);
            }
        }

        return (bool)$matched && (strlen($matches[0]) == strlen($routePath));
    }

    /**
     * Return true if route support given http method.
     *
     * @param string $method
     *
     * @return bool
     */
    public function support($method) {
        return in_array($method, $this->methods);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function matchRequest(Request $request) {
        return is_a($request, $this->supportedRequest)
            && $this->support($request->getMethod())
            && $this->match($request);
    }
}