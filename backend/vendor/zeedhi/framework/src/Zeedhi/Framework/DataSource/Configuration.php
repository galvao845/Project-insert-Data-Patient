<?php
namespace Zeedhi\Framework\DataSource;

/**
 * Class Configuration
 *
 * @package Zeedhi\Framework\DataSource
 */
class Configuration
{

    /** @var string */
    protected $name;
    /** @var string */
    protected $tableName;
    /** @var array */
    protected $columns;
    /** @var array */
    protected $primaryKeyColumns;
    /** @var string */
    protected $sequentialColumn;
    /** @var array */
    protected $relations;
    /** @var string */
    protected $query;
    /** @var string */
    protected $dataColumnsByColumn;
    /** @var array */
    protected $orderBy = array();
    /** @var array */
    protected $groupBy = array();
    /** @var int Zero (0) value will be treated as unlimited. */
    protected $resultSetLimit = 0;
    /** @var array */
    protected $conditions = array();

    protected static $collectionsMap = array();

    public function __construct($name, $columns) {
        $this->name = $name;
        $this->setColumns($columns);
        $this->primaryKeyColumns = array();
        $this->relations = array();
    }

    protected function setPrimaryKeyColumns($primaryKeyColumns) {
        foreach ($primaryKeyColumns as $pkColumn) {
            if (!in_array($pkColumn, $this->columns)) {
                throw Exception::pkColumnNotFoundInColumnList($pkColumn, $this->name);
            }
        }

        $this->primaryKeyColumns = $primaryKeyColumns;
    }

    protected function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    protected function setSequentialColumn($sequentialColumn) {
        if (in_array($sequentialColumn, $this->primaryKeyColumns)) {
            $this->sequentialColumn = $sequentialColumn;
        } else {
            throw Exception::sequentialColumnMustBeAPkColumn($this->name, $sequentialColumn);
        }
    }

    protected function setRelations($relations) {
        $this->relations = $relations;
    }

    protected function setQuery($query) {
        $this->query = $query;
    }

    protected static function openFile($dirLocation, $dataSourceName) {
        $pathFile = realpath($dirLocation).DIRECTORY_SEPARATOR.$dataSourceName.'.json';

        if (!file_exists($pathFile)) {
            throw Exception::invalidFileName($dataSourceName);
        }

        $json = file_get_contents($pathFile);
        $dataSourceConfig = json_decode($json, true);
        if(!isset($dataSourceConfig[$dataSourceName])) {
            throw Exception::invalidDataSourceName($dataSourceName);
        }

        return $dataSourceConfig[$dataSourceName];

    }

    /**
     * @param string $dirLocation
     * @param string $dataSourceName
     *
     * @return Configuration
     *
     * @throws Exception With invalid data source config file.
     */
    public static function factoryFromFileLocation($dirLocation, $dataSourceName) {
        $dataSourceConfig = static::openFile($dirLocation, $dataSourceName);
        return static::factoryFromJsonData($dataSourceConfig, $dataSourceName);
    }

    /**
     * @param $dataSourceConfig
     * @param $dataSourceName
     * @return static
     * @throws Exception
     */
    protected static function factoryFromJsonData($dataSourceConfig, $dataSourceName) {
        $columns = $dataSourceConfig['columns'];

        $instance = new static($dataSourceName, $columns);

        if (isset($dataSourceConfig['tableName'])) {
            $instance->setTableName($dataSourceConfig['tableName']);
            if (isset($dataSourceConfig['primaryKeys'])) {
                $instance->setPrimaryKeyColumns($dataSourceConfig['primaryKeys']);
                if (isset($dataSourceConfig['sequentialColumn'])) {
                    $instance->setSequentialColumn($dataSourceConfig['sequentialColumn']);
                }
            }

            if (isset($dataSourceConfig['relations'])) {
                $instance->setRelations($dataSourceConfig['relations']);
            }
        }

        if (isset($dataSourceConfig['query'])) {
            $instance->setQuery($dataSourceConfig['query']);
        }

        if (isset($dataSourceConfig['orderBy'])) {
            $instance->orderBy = $dataSourceConfig['orderBy'];
        }

        if (isset($dataSourceConfig['groupBy'])) {
            $instance->groupBy = $dataSourceConfig['groupBy'];
        }

        if (isset($dataSourceConfig['resultSetLimit'])) {
            $instance->resultSetLimit = $dataSourceConfig['resultSetLimit'];
        }

        if (isset($dataSourceConfig['conditions'])) {
            $instance->conditions = $dataSourceConfig['conditions'];
        }

        return $instance;
    }

    public static function factoryFromRelation($relationMetaData) {
        $relationConfig = new static($relationMetaData['targetTable'], $relationMetaData['targetColumns']);
        $relationConfig->setTableName($relationMetaData['targetTable']);
        $relationConfig->setPrimaryKeyColumns($relationMetaData['targetColumns']);
        $relationConfig->setSequentialColumn($relationMetaData['targetSequentialColumn']);
        return $relationConfig;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getPrimaryKeyColumns()
    {
        return $this->primaryKeyColumns;
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return string
     */
    public function getSequentialColumn()
    {
        return $this->sequentialColumn;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return bool
     */
    public function hasQuery() {
        return $this->query !== null;
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @param string $dataColumn The data column name.
     *
     * @throws Exception Data column not found.
     *
     * @return string The column name.
     */
    public function getColumnByDataColumn($dataColumn) {
        if (isset($this->columns[$dataColumn])) {
            return $this->columns[$dataColumn];
        }

        throw Exception::dataColumnDoesNotExist($dataColumn, $this->name);
    }

    public function getDataColumnByColumn($column) {
        if (isset($this->dataColumnsByColumn[$column])) {
            return $this->dataColumnsByColumn[$column];
        }

        throw Exception::dataColumnDoesNotExist($column, $this->name);
    }

    public function getDataColumns() {
        return array_keys($this->columns);
    }

    /**
     * @param array $columns
     */
    protected function setColumns($columns) {
        $this->dataColumnsByColumn = $this->columns = array();
        foreach ($columns as $dataColumnName => $columnName) {
            if (is_numeric($dataColumnName)) { // This allow BC when dataColumnName became necessary!
                $dataColumnName = $columnName;
            }

            $this->columns[$dataColumnName] = $columnName;
            $this->dataColumnsByColumn[$columnName] = $dataColumnName;
        }
    }

    public function getColumnsForResultSet() {
        return $this->hasQuery()
            ? $this->getDataColumns()
            : $this->getColumns();
    }

    public function getOrderBy() {
        return $this->orderBy;
    }

    public function getGroupBy() {
        return $this->groupBy;
    }

    public function getResultSetLimit() {
        return $this->resultSetLimit;
    }

    public function getConditions() {
        return $this->conditions;
    }

}