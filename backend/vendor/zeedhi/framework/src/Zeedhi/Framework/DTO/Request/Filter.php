<?php
namespace Zeedhi\Framework\DTO\Request;

use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DTO\Request;

/**
 * Class Filter
 *
 * Class to transport the request of the filter type
 *
 * @package Zeedhi\Framework\DTO\Request
 */
class Filter extends Request {

    /** @var FilterCriteria */
    protected $filterCriteria;

    /**
     * @param FilterCriteria $filterCriteria The filter criteria to be used.
     * @param string         $method         The request method used.
     * @param string         $routePath      The route path called.
     * @param string         $userId         The user-Id responsible the request.
     */
    function __construct($filterCriteria, $method, $routePath, $userId) {
        $this->filterCriteria = $filterCriteria;
        parent::__construct($method, $routePath, $userId);
    }

    /**
     * Returns a instance of \Zeedhi\Framework\DataSource\FilterCriteria of the request
     *
     * @return \Zeedhi\Framework\DataSource\FilterCriteria
     */
    public function getFilterCriteria() {
        return $this->filterCriteria;
    }
} 