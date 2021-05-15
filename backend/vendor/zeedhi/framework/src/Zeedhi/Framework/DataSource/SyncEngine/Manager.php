<?php
namespace Zeedhi\Framework\DataSource\SyncEngine;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager\Doctrine\NameProvider;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\Kernel;

class Manager implements \Zeedhi\Framework\DataSource\Manager {

    /** @var SyncEngine */
    protected $syncEngine;
    /** @var \Zeedhi\Framework\DataSource\Manager */
    protected $manager;
    /** @var \Zeedhi\Framework\Kernel */
    protected $kernel;
    /** @var NameProvider */
    protected $nameProvider;

    const ALL_DATA = '__ALL';

    public function __construct(
        \Zeedhi\Framework\DataSource\Manager $manager,
        SyncEngine $syncEngine,
        Kernel $kernel,
        NameProvider $nameProvider
    ) {
        $this->manager = $manager;
        $this->syncEngine = $syncEngine;
        $this->kernel = $kernel;
        $this->nameProvider = $nameProvider;
    }

    /**
     * Persist all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     */
    public function persist(DataSet $dataSet) {
        $syncDataSet = $this->synchronizeDataSet($dataSet);
        $persistedRows = $this->manager->persist($syncDataSet);
        $unSyncPersistedRows = $this->unSyncPkRows($dataSet, $persistedRows);
        return $unSyncPersistedRows;
    }

    /**
     * Delete all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     */
    public function delete(DataSet $dataSet) {
        $syncDataSet = $this->synchronizeDataSet($dataSet);
        $deletedRows = $this->manager->delete($syncDataSet);
        $unSyncPersistedRows = $this->unSyncPkRows($dataSet, $deletedRows);
        return $unSyncPersistedRows;
    }

    /**
     * Return a DataSet with rows that match the given criteria.
     *
     * @param FilterCriteria $filterCriteria
     *
     * @return DataSet The result of the filter criteria.
     */
    public function findBy(FilterCriteria $filterCriteria) {
        $syncFilterCriteria = $this->syncFilterCriteria($filterCriteria);
        $dataSet = $this->manager->findBy($syncFilterCriteria);
        $unSyncRows = $this->unSynchronizeRows($dataSet);
        return new DataSet($dataSet->getDataSourceName(), $unSyncRows);
    }

    /**
     * @return mixed
     */
    protected function getUserId() {
        return $this->kernel->getRequest()->getUserId();
    }

    /**
     * @param DataSet $dataSet
     *
     * @return DataSet
     */
    protected function synchronizeDataSet(DataSet $dataSet) {
        $dataSourceName = $dataSet->getDataSourceName();
        $configuration = $this->nameProvider->getDataSourceByName($dataSourceName);
        $originalRows = array();
        foreach($dataSet->getRows() as $key => $row) {
            $originalRows[$key] = $row instanceof Row ? $row->getArrayCopy() : $row;
        }
        $syncRows = $this->syncEngine->synchronizeRows($configuration, $originalRows, $this->getUserId());
        $syncDataSet = new DataSet($dataSourceName, $syncRows);
        return $syncDataSet;
    }

    /**
     * @param DataSet $dataSet
     * @param $persistedRows
     * @return array
     */
    protected function unSyncPkRows(DataSet $dataSet, $persistedRows) {
        $dataSourceName = $dataSet->getDataSourceName();
        $configuration = $this->nameProvider->getDataSourceByName($dataSourceName);
        return $this->syncEngine->unSyncPkRows($persistedRows, $configuration, $dataSet->getRows());
    }

    /**
     * @param DataSet $dataSet
     *
     * @return array with unsynchronized rows.
     */
    protected function unSynchronizeRows($dataSet) {
        $dataSourceName = $dataSet->getDataSourceName();
        $configuration = $this->nameProvider->getDataSourceByName($dataSourceName);
        return $this->syncEngine->unSynchronizeRows($configuration, $dataSet->getRows(), $this->getUserId());
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @return FilterCriteria
     */
    protected function syncFilterCriteria(FilterCriteria $filterCriteria) {
        $config = $this->nameProvider->getDataSourceByName($filterCriteria->getDataSourceName());
        return $this->syncEngine->syncFilterCriteria($filterCriteria, $config, $this->getUserId());
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