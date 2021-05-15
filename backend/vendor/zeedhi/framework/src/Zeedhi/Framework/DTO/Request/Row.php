<?php
namespace Zeedhi\Framework\DTO\Request;

use Zeedhi\Framework\DTO\Request;

/**
 * Class Row
 *
 * Class to transport the request of the row type
 *
 * @package Zeedhi\Framework\DTO\Request
 */
class Row extends Request {

    /** @var array */
    protected $row;

    /**
     * Constructor
     *
     * @param mixed  $row
     * @param string $method    The request method used.
     * @param string $routePath The route path called.
     * @param string $userId    The user-Id responsible the request.
     */
    function __construct($row, $method, $routePath, $userId) {
        $this->row = $row;
        parent::__construct($method, $routePath, $userId);
    }

    /**
     * Return the row of the request
     *
     * @return mixed
     */
    public function getRow() {
        return $this->row;
    }

} 