<?php
namespace Zeedhi\Framework\DataSource\Manager;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\ParameterTypeInferer;
use Zeedhi\Framework\DataSource\AssociatedWithDataSource;
use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\Manager\Doctrine\NameProvider;
use Zeedhi\Framework\DataSource\Operator\DefaultOperator;
use Zeedhi\Framework\DataSource\ParameterBag;

abstract class AbstractManager implements Manager{

    /** @var NameProvider */
    protected $nameProvider;
    /** @var ParameterBag */
    protected $parameterBag;
    /** @var Configuration */
    protected $dataSourceConfig;

    const ALL_DATA = "__ALL";

    public function __construct(NameProvider $nameProvider, ParameterBag $parameterBag) {
        $this->nameProvider = $nameProvider;
        $this->parameterBag = $parameterBag;
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

    /**
     * @param AssociatedWithDataSource $associatedWithDataSource
     */
    protected function loadCurrentDataSource(AssociatedWithDataSource $associatedWithDataSource) {
        $this->dataSourceConfig = $this->nameProvider->getDataSourceByName($associatedWithDataSource->getDataSourceName());
    }

    /**
     * @param $row
     * @return array
     */
    protected function getPrimaryKeyValueFromRow($row) {
        $persistedRow = array();
        foreach ($this->dataSourceConfig->getPrimaryKeyColumns() as $columnName) {
            $persistedRow[$columnName] = $row[$columnName];
        }

        return $persistedRow;
    }


    abstract protected function beginTransaction();
    abstract protected function commit();
    abstract protected function rollback();
    /**
     * @param array $row
     */
    abstract protected function persistRow($row);

    protected function persistRows(DataSet $dataSet) {
        $persistedRows = array();
        foreach ($dataSet->getRows() as $key => $row) {
            $this->persistRow($row);
            $persistedRows[$key] = $this->getPrimaryKeyValueFromRow($row);
        }

        return $persistedRows;
    }

    /**
     * Persist all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     *
     * @throws
     */
    public function persist(DataSet $dataSet) {
        $this->loadCurrentDataSource($dataSet);
        $this->beginTransaction();
        try {
            $persistedRows = $this->persistRows($dataSet);
            $this->commit();
            return $persistedRows;
        } catch (\Exception $e) {
            $this->rollback();
            throw Exception::errorExecutingQuery($e);
        }
    }

    abstract protected function deleteRow($row);

    /**
     * @param DataSet $dataSet
     * @return array
     */
    protected function deleteRows(DataSet $dataSet) {
        $deletedRows = array();
        foreach ($dataSet->getRows() as $key => $row) {
            // New rows doesn't need to be deleted, since their do not exist.
            if ($row['__is_new'] === false) {
                $this->deleteRow($row);
                $deletedRows[$key] = $this->getPrimaryKeyValueFromRow($row);
            }
        }

        return $deletedRows;
    }

    /**
     * Delete all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     *
     * @throws Exception
     */
    public function delete(DataSet $dataSet) {
        $this->loadCurrentDataSource($dataSet);
        $this->beginTransaction();
        try {
            $deletedRows = $this->deleteRows($dataSet);
            $this->commit();
            return $deletedRows;
        } catch (\Exception $e) {
            $this->rollback();
            throw Exception::errorExecutingQuery($e);
        }
    }

    /**
     * @param FilterCriteria $filterCriteria
     *
     * @return array[] The rows
     */
    abstract protected function retrieveRows(FilterCriteria $filterCriteria);

    /**
     * @param array $rows
     *
     * @return array
     */
    protected function addIsNewColumn($rows) {
        foreach ($rows as &$row) {
            $row['__is_new'] = false;
        }

        return $rows;
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @param QueryBuilder $query
     */
    protected function processPagination(FilterCriteria $filterCriteria, QueryBuilder $query) {
        if ($filterCriteria->isPaginated()) {
            $query->setFirstResult($filterCriteria->getFirstResult());
            $query->setMaxResults($filterCriteria->getPageSize());
        } else {
            if ($maxResults = $this->dataSourceConfig->getResultSetLimit()) {
                $query->setMaxResults($maxResults);
            }
        }
    }

    /**
     * @param QueryBuilder $query
     * @param $orderBy
     */
    protected function addOrderByToQuery(QueryBuilder $query, $orderBy) {
        foreach ($orderBy as $columnName => $direction) {
            $query->addOrderBy($columnName, $direction);
        }
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @param QueryBuilder $query
     */
    protected function processOrderBy(FilterCriteria $filterCriteria, QueryBuilder $query) {
        $this->addOrderByToQuery($query, $this->dataSourceConfig->getOrderBy());
        $this->addOrderByToQuery($query, $filterCriteria->getOrderBy());
    }

    /**
     * @param QueryBuilder $query
     * @param $groupBy
     */
    protected function addGroupByToQuery(QueryBuilder $query, $groupBy) {
        $query->addGroupBy($groupBy);
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @param QueryBuilder $query
     */
    protected function processGroupBy(FilterCriteria $filterCriteria, QueryBuilder $query) {
        $this->addGroupByToQuery($query, $this->dataSourceConfig->getGroupBy());
        $this->addGroupByToQuery($query, $filterCriteria->getGroupBy());
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @param QueryBuilder $query
     * @return array
     */
    protected function buildQueryConditions(FilterCriteria $filterCriteria, QueryBuilder $query) {
        $params = array();
        foreach($filterCriteria->getConditions() as $condition) {
            DefaultOperator::factoryFromStringRepresentation($condition['operator'], $this->dataSourceConfig)
                ->addConditionToQuery($condition, $query, $params);
        }

        return $params;
    }

    /**
     * @param QueryBuilder $query
     * @param $params
     * @return mixed
     */
    protected function completeParamsWithParameterBag(QueryBuilder $query, $params) {
        $matches = array();
        preg_match_all('/:([A-Za-z0-9_]*)/', $query->getSQL(), $matches);
        foreach ($matches[1] as $paramName) {
            if (!isset($params[$paramName])) {
                $params[$paramName] = $this->parameterBag->get($paramName);
            }
        }

        return $params;
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @param QueryBuilder $query
     *
     * @return array
     */
    protected function processWhereClause(FilterCriteria $filterCriteria, QueryBuilder $query) {
        $params = $this->buildQueryConditions($filterCriteria, $query);
        if ($filterCriteria->hasWhereClause()) {
            $query->andWhere($filterCriteria->getWhereClause());
            foreach ($filterCriteria->getWhereClauseParams() as $bindColumnName => $value) {
                $bindColumnName = ltrim($bindColumnName, ':');
                $params[$bindColumnName] = $value;
            }
        }

        foreach ($this->dataSourceConfig->getConditions() as $condition) {
            $query->andWhere($condition);
        }

        $params = $this->completeParamsWithParameterBag($query, $params);
        return $params;
    }

    /**
     * Apply FilterCriteria conditions, whereClause and pagination to query.
     * Return a array of parameters needed to execute the query.
     *
     * @param FilterCriteria $filterCriteria
     * @param QueryBuilder   $query
     *
     * @return array
     */
    protected function processFilterConditions(FilterCriteria $filterCriteria, QueryBuilder $query) {
        $params = $this->processWhereClause($filterCriteria, $query);
        $this->processPagination($filterCriteria, $query);
        $this->processGroupBy($filterCriteria, $query);
        $this->processOrderBy($filterCriteria, $query);
        return $params;
    }

    /**
     * @param $params
     *
     * @return array
     */
    protected function inferTypes($params) {
        $types = array();
        foreach ($params as $name => $value) {
            $types[$name] = ParameterTypeInferer::inferType($value);
        }
        return $types;
    }

    /**
     * Return a DataSet with rows that match the given criteria.
     *
     * @param FilterCriteria $filterCriteria
     *
     * @return DataSet The result of the filter criteria.
     */
    public function findBy(FilterCriteria $filterCriteria) {
        $this->loadCurrentDataSource($filterCriteria);
        $rows = $this->retrieveRows($filterCriteria);
        $rows = $this->addIsNewColumn($rows);
        return new DataSet($filterCriteria->getDataSourceName(), $rows);
    }
}