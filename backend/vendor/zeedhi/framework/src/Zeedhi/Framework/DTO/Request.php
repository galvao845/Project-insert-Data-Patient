<?php
namespace Zeedhi\Framework\DTO;

class Request {

    const TYPE_FILTER   = 'FilterData';
    const TYPE_ROW      = 'Row';
    const TYPE_DATA_SET = 'DataSet';
    const TYPE_EMPTY    = 'Empty';

    /** @var string The route path called. */
    protected $routePath;
    /** @var string The request method used. */
    protected $method;
    /** @var string The user-Id responsible the request. */
    protected $userId;
    /** @var array */
    protected $parameters;

    /**
     * Construct...
     *
     * @param string $method    The request method used.
     * @param string $routePath The route path called.
     * @param string $userId    The user-Id responsible the request.
     */
    function __construct($method, $routePath, $userId) {
        $this->method = $method;
        $this->routePath = $routePath;
        $this->userId = $userId;
        $this->parameters = array();
    }

    /**
     * Returns the route path called
     *
     * @return string
     */
    public function getRoutePath() {
        return $this->routePath;
    }

    /**
     * Returns the method used in request
     *
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Returns the user-Id responsible the request
     *
     * @return string
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getParameter($name) {
        if(!isset($this->parameters[$name])) {
            throw Exception::parameterNotFound($name);
        }
        return $this->parameters[$name];
    }

    /**
     * @param string     $name
     * @param string|int $value
     */
    public function setParameter($name, $value) {
        $this->parameters[$name] = $value;
    }

    /**
     * getParameters
     * Retrieve all request parameters
     *
     * @return mixed[]
     */
    public function getParameters() {
        return $this->parameters;
    }
}