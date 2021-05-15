<?php
namespace Zeedhi\Framework\Remote;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Row;

class RequestFactory {

    protected $userId;

    /**
     * setUserId
     *
     * @param string $userId Unique device identifier used by Zeedhi
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }

    /**
     * createEmptyRequest
     *
     * Create a DTO\Request request.
     *
     * @param string $method The request method used.
     * @param string $route  The route path called.
     *
     * @return Request
     */
    public function createEmptyRequest($method, $route) {
        return new Request($method, $route, $this->userId);
    }

    /**
     * createDataSetRequest
     *
     * Create a DTO\Request\Dataset.
     *
     * @param string  $method  The request method used.
     * @param string  $route   The route path called.
     * @param DataSet $dataSet The dataset to be sent.
     *
     * @return Request\DataSet
     */
    public function createDataSetRequest($method, $route, DataSet $dataSet) {
        return new Request\DataSet($dataSet, $method, $route, $this->userId);
    }

    /**
     * createFilterRequest
     *
     * Create a DTO\Request\Filter.
     *
     * @param string         $method         The request method used.
     * @param string         $route          The route path called.
     * @param FilterCriteria $filterCriteria The filter criteria to be sent.
     *
     * @return Request\Filter
     */
    public function createFilterRequest($method, $route, FilterCriteria $filterCriteria) {
        return new Request\Filter($filterCriteria, $method, $route, $this->userId);
    }

    /**
     * createRowRequest
     *
     * Create a DTO\Request\Row.
     *
     * @param string    $method The request method used.
     * @param string    $route  The route path called.
     * @param array|Row $row    The row to be send.
     *
     * @return Request\Row
     */
    public function createRowRequest($method, $route, $row) {
        return new Request\Row($row, $method, $route, $this->userId);
    }

}