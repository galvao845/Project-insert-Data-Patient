<?php
namespace Zeedhi\Framework\DataSource\Manager\Security;

use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\Manager\Security;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\DataSet;

class ManagerImpl implements Manager {

    /** @var Manager */
    public $manager;
    /** @var string[] */
    const VALID_OPERATORS = array(
        FilterCriteria::EQ,
        FilterCriteria::NEQ,
        FilterCriteria::LT,
        FilterCriteria::LTE,
        FilterCriteria::GT,
        FilterCriteria::GTE,
        FilterCriteria::IS,
        FilterCriteria::IN,
        FilterCriteria::NOT_IN,
        FilterCriteria::LIKE,
        FilterCriteria::LIKE_I,
        FilterCriteria::NOT_LIKE,
        FilterCriteria::IS_NULL,
        FilterCriteria::IS_NOT_NULL,
        FilterCriteria::BETWEEN,
        FilterCriteria::NOT_BETWEEN,
        FilterCriteria::LIKE_ALL,
        FilterCriteria::MAPPED_LIKE_ALL
    );

    const ALL_DATA = "__ALL";

    /**
     * Constructor
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager) {
        $this->manager = $manager;
    }

    /**
     * @param string $groupBy
     *
     * @throws Exception
     */
    public function testGroupBySafety($groupBy){
        preg_match('/^([A-Za-z0-9_])+$/', $groupBy, $matches);
        if (empty($matches)) {
            throw Exception::groupByNotSafe($groupBy);
        }
    }

    /**
     * Tests if the operators of the filter are valid.
     *
     * @param string $operator The filter.
     *
     * @throws Exception
     */
    protected function testOperatorSafety($operator){
        if(!in_array($operator, self::VALID_OPERATORS)){
            throw Exception::operatorNotSafe($operator);
        }
    }

    /**
     * Test if the columns used with the operator LIKE_ALL are safe.
     *
     * @param string $columnName The column name.
     */
    protected function testLikeAllOperatorSafety($columnName){
        if ($columnName !== "*" ) {
            $columns = explode("|", $columnName);
            foreach ($columns as $column) {
                $this->testDefaultOperatorsSafety($column);
            }
        }
    }

    /**
     * Test if the columns used with the operator MAPPED_LIKE_ALL are safe.
     *
     * @param string $columnName The column name.
     */
    protected function testMappedLikeAllOperatorSafety($columnName){
        if ($columnName !== "*" ) {
            $columnsList = explode('#', $columnName);
            $columns = explode('|', $columnsList[0]);
            if(isset($columnsList[1])){
                $columns = array_merge($coluns, explode('&', $columnsList[1]));
            }
            foreach ($columns as $column) {
                $this->testDefaultOperatorsSafety($column);
            }
        }
    }

    /**
     * Test default of the column name.
     *
     * @param string $columnName The column name.
     *
     * @throws Exception;
     */
    protected function testDefaultOperatorsSafety($columnName){
        preg_match('/^([A-Za-z0-9_])+$/', $columnName, $matches);
        if(empty($matches)) {
            throw Exception::columnNameNotSafe($columnName);
        }
    }


    /**
     * Check safety of given operator and column name.
     *
     * @param string $columnName
     * @param string $operator
     *
     * @throws Exception When operator or column names was considered not safe.
     */
    protected function testColumnNameSafety($columnName, $operator){
        switch ($operator) {
            case FilterCriteria::LIKE_ALL:
                $this->testLikeAllOperatorSafety($columnName);
                break;
            case FilterCriteria::MAPPED_LIKE_ALL:
                $this->testMappedLikeAllOperatorSafety($columnName);
                break;
            default:
                $this->testDefaultOperatorsSafety($columnName);
                break;
        }
    }

    /**
     * Ensure an valid filter
     *
     * @param FilterCriteria $filterCriteria The filter.
     */
    protected function testFilterCriteriaSafety(FilterCriteria $filterCriteria){

        $groupByParams = $filterCriteria->getGroupBy();
        foreach ($groupByParams as $groupBy) {
            $this->testGroupBySafety($groupBy);
        }

        $conditions = $filterCriteria->getConditions();
        foreach ($conditions as $condition) {
            $operator = $condition["operator"];
            $columnName = $condition["columnName"];

            $this->testOperatorSafety($operator);
            $this->testColumnNameSafety($columnName, $operator);
        }
    }

    /**
     * Returns a empty array for when the filter is not valid.
     * This empty return makes harder to do SQL injection once you have no errors and information
     * about the backend on the response.
     *
     * @param  FilterCriteria $filterCriteria The filter.
     *
     * @return DataSet An empty data set.
     */
    protected function returnEmptyDataSet(FilterCriteria $filterCriteria){
        $arr = array();
        return new DataSet($filterCriteria->getDataSourceName(), $arr);
    }

    /**
     * Return a DataSet with rows that match the given criteria or an empty array if the filter criteria is not valid.
     *
     * @param  FilterCriteria $filterCriteria The filter.
     *
     * @return DataSet The result of the filter criteria.
     */
    public function findBy(FilterCriteria $filterCriteria){
        try {
            $this->testFilterCriteriaSafety($filterCriteria);
            return $this->manager->findBy($filterCriteria);
        }
        catch (Exception $e) {
            return $this->returnEmptyDataSet($filterCriteria);
        }
    }

    /**
     * Persist all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     */
    public function persist(DataSet $dataSet) {
        return $this->manager->persist($dataSet);
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