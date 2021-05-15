<?php
namespace Zeedhi\Framework\DTO\Request;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DataSource;

/**
 * Class DataSet
 *
 * Class to transport the request of the DataSet type
 *
 * @package Zeedhi\Framework\DTO\Request
 */
class DataSet extends Request {

    /** @var DataSet */
    protected $dataSet;

    /**
     * Constructor
     *
     * @param DataSource\DataSet $dataSet   The dataSet to be used in
     * @param string             $method    The request method used.
     * @param string             $routePath The route path called.
     * @param string             $userId    The user-Id responsible the request.
     */
    public function __construct(DataSource\DataSet $dataSet, $method, $routePath, $userId) {
        $this->dataSet = $dataSet;
        parent::__construct($method, $routePath, $userId);
    }

    /**
     * Returns a instance of \Zeedhi\Framework\DataSource\DataSet of the request
     *
     * @return DataSource\DataSet
     */
    public function getDataSet() {
        return $this->dataSet;
    }
}