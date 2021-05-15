<?php
namespace Zeedhi\Framework\DTO\Response;
/**
 * Class Method
 *
 * Contains the methods that are returned in response
 *
 * @package Zeedhi\Framework\DTO\Response
 */
class Method {

    /** @var string */
    protected $name;
    /** @var array */
    protected $parameters;

    /**
     * Constructor
     *
     * @param string  $name       The name of method that are returned in response
     * @param array   $parameters Optional
     */
    function __construct($name, $parameters = array())
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * Returns name of method.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns parameters of method.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}