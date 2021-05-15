<?php
namespace Zeedhi\Framework\DataSource\Manager\LogicalToRealDelete;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;

class ManagerImpl implements Manager {

	/** @var Manager */
	protected $manager;
	/** @var string */
	protected $logicalDeleteColumn;
	/** @var mixed */
	protected $deletedValue;
	/** @var mixed */
	protected $nonDeletedValue;

	const ALL_DATA = "__ALL";

	public function __construct(Manager $manager, $logicalDeleteColumn, $deletedValue, $nonDeletedValue = null) {
		$this->manager = $manager;
		$this->logicalDeleteColumn = $logicalDeleteColumn;
		$this->deletedValue = $deletedValue;
		$this->nonDeletedValue = $nonDeletedValue !== null ? $nonDeletedValue : !$deletedValue;
	}


	/**
	 * Persist all given rows in DataSet.
	 *
	 * @param DataSet $dataSet The collection and description of rows.
	 *
	 * @return array Rows with primary key columns values.
	 */
	public function persist(DataSet $dataSet) {
		$rowsToPersist = array();
		$rowsToDelete = array();
		foreach ($dataSet->getRows() as $row) {
			if ($this->isDeleted($row)) {
				$rowsToDelete[] = $row;
			} else {
				$rowsToPersist[] = $row;
			}
		}
		$dataSetToDelete = new DataSet($dataSet->getDataSourceName(), $rowsToDelete);
		$dataSetToPersist = new DataSet($dataSet->getDataSourceName(), $rowsToPersist);
		$deletedRows = $this->manager->delete($dataSetToDelete);
		$persistedRows = $this->manager->persist($dataSetToPersist);
		return array_merge($deletedRows, $persistedRows);
	}

	/**
	 * Delete all given rows in DataSet.
	 *
	 * @param DataSet $dataSet The collection and description of rows.
	 *
	 * @return array Rows with primary key columns values.
	 */
	public function delete(DataSet $dataSet) {
		return $this->manager->delete($dataSet);
	}

	/**
	 * Return a DataSet with rows that match the given criteria.
	 *
	 * @param FilterCriteria $filterCriteria
	 *
	 * @return DataSet The result of the filter criteria.
	 */
	public function findBy(FilterCriteria $filterCriteria) {
		$dataSet = $this->manager->findBy($filterCriteria);
		$rows = array();
		foreach ($dataSet->getRows() as $row) {
			$row[$this->logicalDeleteColumn] = $this->nonDeletedValue;
			$rows[] = $row;
		}
		return new DataSet($dataSet->getDataSourceName(), $rows);
	}

	/**
	 * @param $row
	 *
	 * @return bool
	 */
	private function isDeleted($row) {
		return $row[$this->logicalDeleteColumn] === $this->deletedValue;
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