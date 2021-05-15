<?php
namespace Zeedhi\Framework\DataSource\Manager\Remote;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DataSource\AssociatedWithDataSource;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\Remote\Server;
use Zeedhi\Framework\Remote\RequestFactory;

class ManagerImpl implements Manager {

    /** @var Server */
    protected $remoteServer;
    /** @var RequestFactory */
    protected $requestFactory;
    /** @var RequestProvider */
    protected $requestProvider;

    const ALL_DATA = "__ALL";

    /**
     * __construct
     *
     * @param Server          $remoteServer
     * @param RequestFactory  $requestFactory
     * @param RequestProvider $requestProvider
     */
    public function __construct(Server $remoteServer, RequestFactory $requestFactory, RequestProvider $requestProvider) {
        $this->remoteServer    = $remoteServer;
        $this->requestFactory  = $requestFactory;
        $this->requestProvider = $requestProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(DataSet $dataSet) {
        return $this->proxyDataSetRequest($dataSet);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DataSet $dataSet) {
        return $this->proxyDataSetRequest($dataSet);
    }

    protected function proxyDataSetRequest(DataSet $dataSet) {
        $remoteRequest = $this->factoryDataSetRequest($dataSet);
        $remoteResponse = $this->remoteServer->request($remoteRequest);
        return $this->getDataSetFromResponse($dataSet->getDataSourceName(), $remoteResponse)->getRows();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(FilterCriteria $filterCriteria) {
        $remoteRequest = $this->factoryFilterRequest($filterCriteria);
        $remoteResponse = $this->remoteServer->request($remoteRequest);

        return $this->getDataSetFromResponse($filterCriteria->getDataSourceName(), $remoteResponse);
    }

    protected function getDataSetFromResponse($dataSourceName, Response $response) {
        $this->checkForRemoteError($response);
        $dataSets = $response->getDataSets();
        return $this->getDataSet($dataSourceName, $dataSets);
    }

    protected function checkForRemoteError(Response $response) {
        $remoteError = $response->getError();
        if ($remoteError !== null) {
            throw Exception::errorOnRemoteServer($remoteError->getMessage());
        }
    }

    protected function getDataSet($dataSetName, $dataSetList) {
        foreach ($dataSetList as $dataSet) {
            if ($dataSet->getDataSourceName() === $dataSetName) {
                return $dataSet;
            }
        }

        throw Exception::dataSetNotFound($dataSetName);
    }

    protected function createRemoteRequest(callable $cbk, $obj) {
        $request = $this->requestProvider->getRequest();
        $userId = $request->getUserId();
        $method = $request->getMethod();
        $route  = $request->getRoutePath();
        $this->requestFactory->setUserId($userId);
        return call_user_func($cbk, $method, $route, $obj);
    }

    protected function factoryDataSetRequest(DataSet $dataSet) {
        return $this->createRemoteRequest(array($this->requestFactory, 'createDataSetRequest'), $dataSet);
    }


    protected function factoryFilterRequest(FilterCriteria $filterCriteria) {
        return $this->createRemoteRequest(array($this->requestFactory, 'createFilterRequest'), $filterCriteria);
    }

    /**
	 * Return a populated dataSet.
	 *
	 * Verify if the dataSet contains a ALL_DATA's flag and populate it.
	 *
	 * @param DataSet $dataSet
	 *
	 * @return DataSet $dataSet
	 */
	public function populateDataSet(DataSet $dataSet){
        $rows = $dataSet->getRows();
        $dataSourceName = $dataSet->getDataSourceName();
		foreach($rows[0] as $column => $value){
            if(is_array($value) && isset($value[self::ALL_DATA])){
                if(isset($data)){
                    $rows[0][$column] = $data;
                } else {
                    $filterCriteria = $this->buildAllDataFilter($rows, $value, $column, $dataSourceName);
                    $data = $rows[0][$column] = $this->findBy($filterCriteria)->getRows();
                }
            }
		}
		return new DataSet($dataSourceName, $rows);
    }

    /**
     * Return a filterCriteria.
     *
     * Build a filterCriteria based on dataSourceFilter.
     *
     * @param $rows
     * @param $value
     * @param $column
     *
     * @return FilterCriteria $filterCriteria
     */
    public function buildAllDataFilter($rows, $value, $column, $dataSourceName){
        $filterCriteria = new FilterCriteria($dataSourceName);
        if(isset($rows[0][$column . '_EXCEPT']) && !empty($rows[0][$column . '_EXCEPT'])){
            $exceptionFilter = array();
            foreach($rows[0][$column . '_EXCEPT'] as $exceptRow){
                $exceptionFilter[] = $exceptRow;
            }
            $filterCriteria->addCondition($column, "NOT_IN", $exceptionFilter);
        }
        if(!empty($value[self::ALL_DATA])){
            foreach($value[self::ALL_DATA] as $filter){
                $filterCriteria->addCondition($filter["name"], $filter["operator"], $filter["value"]);
            }
        }
        return $filterCriteria;
    }
}