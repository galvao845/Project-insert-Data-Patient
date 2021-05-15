<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo;

use Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr;
use Zeedhi\Framework\DataSource\Manager\Mongo\Types\Type;
use Zeedhi\Framework\DataSource\AssociatedWithDataSource;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DB\Mongo\Mongo;


class ManagerImpl implements Manager {

    /** @var Mongo */
    protected $mongo;
    /** @var NameProvider */
    protected $nameProvider;
    /** @var Configuration */
    protected $dataSourceConfig;
    /** @var string Table name got from loadCurrentDataSource */
    protected $tableName;
    /** @var string[] Column names of the primary keys */
    protected $localPrimaryKeys = array();
    /** @var array */
    protected $internalCollections = array();
    /** @var Configuration */
    protected $wrapperCollection;
    /** @var array */
    protected $typesMapping;

    const ALL_DATA = "__ALL";

    /**
     * @param Mongo $mongo Handler to mongo connection
     *
     * @param NameProvider $nameProvider Provider for current data source name
     */
    public function __construct(Mongo $mongo, NameProvider $nameProvider) {
        $this->mongo = $mongo;
        $this->nameProvider = $nameProvider;
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
        return $this->persistRows($dataSet);
    }

    /**
     * @param FilterCriteria $filterCriteria
     *
     * @return DataSet
     */
    public function findBy(FilterCriteria $filterCriteria) {
        $this->loadCurrentDataSource($filterCriteria);
        $rows = $this->retrieveRows($filterCriteria);
        return new DataSet($filterCriteria->getDataSourceName(), $rows);
    }

    /**
     * Delete all given rows in DataSet.
     *
     * @param DataSet $dataSet The collection and description of rows.
     *
     * @return array Rows with primary key columns values.
     *
     */
    public function delete(DataSet $dataSet) {
        $this->loadCurrentDataSource($dataSet);
        return $this->deleteRows($dataSet);
    }

    protected function deleteOnInternalCollection($row) {
        $internalCollectionOptions = $this->wrapperCollection->getInternalCollectionOptions($this->tableName);
        $primaryKeys = $internalCollectionOptions['primaryKeysMapping'];

        $tableName = $this->wrapperCollection->getTableName();
        $fieldName = $internalCollectionOptions['fieldName'];
        $criteria = $this->processCriteriaForWrapperByRow($primaryKeys, $row);

        $row = $this->convertRowToDatabaseValue($row);

        $update = array(
            '$pull' => array(
                $fieldName => array('_id' => $row['_id'])
            )
        );

        $this->mongo->update($tableName, $criteria, $update);
    }

    protected function deleteRow($row) {
        if ($this->wrapperCollection) {
            $this->deleteOnInternalCollection($row);
        } else {
            $criteria = $this->buildCriteriaFromRow($row);
            $this->mongo->remove($this->tableName, $criteria);
        }
    }

    /**
     * @param DataSet $dataSet
     *
     * @return array
     */
    protected function deleteRows(DataSet $dataSet) {
        $deletedRows = array();
        foreach ($dataSet->getRows() as $key => $row) {
            $this->deleteRow($row);
            $deletedRows[$key] = $this->getPrimaryKeyValueFromRow($row);
        }
        return $deletedRows;
    }

    protected function loadCurrentDataSource(AssociatedWithDataSource $associatedWithDataSource) {
        $this->dataSourceConfig    = $this->nameProvider->getDataSourceByName($associatedWithDataSource->getDataSourceName());
        $this->tableName           = $this->dataSourceConfig->getTableName();
        $this->localPrimaryKeys    = $this->dataSourceConfig->getPrimaryKeyColumns();
        $this->internalCollections = $this->dataSourceConfig->getInternalCollections();
        $this->wrapperCollection   = $this->dataSourceConfig->getWrapperCollection();
        $this->typesMapping        = $this->dataSourceConfig->getTypesMapping();
    }

    protected function buildCriteriaFromRow($row) {
        $expression = new Expr();
        foreach ($this->localPrimaryKeys as $fieldName) {
            $dbValue = $this->convertToDatabaseValue($fieldName, $row[$fieldName]);
            $expression->field($fieldName)->equals($dbValue);
        }
        return $expression->getQuery();
    }

    protected function convertCollectionTypeToDatabaseValue($fieldName, $rows) {
        $oldMapping = $this->typesMapping;
        $internalCollection = $this->dataSourceConfig->getInternalCollectionForField($fieldName);
        $this->typesMapping = $internalCollection->getTypesMapping();
        $newRows = array();

        foreach($rows as $row) {
            $newRow = array();
            foreach ($row as $fieldName => $value) {
                $newRow[$fieldName] = $this->convertToDatabaseValue($fieldName, $value);
            }
            $newRows[] = $newRow;
        }

        $this->typesMapping = $oldMapping;
        return $newRows;
    }

    protected function convertToDatabaseValue($fieldName, $value) {
        $convertedValue = null;
        if (isset($this->typesMapping[$fieldName])) {
            switch($this->typesMapping[$fieldName]) {
                case Type::MONGO_ID:
                    $convertedValue = Type::getType(Type::MONGO_ID)->convertToDatabaseValue($value);
                    break;
                case Type::MONGO_DATE:
                    $convertedValue = Type::getType(Type::MONGO_DATE)->convertToDatabaseValue($value);
                    break;
                case Type::COLLECTION:
                    $convertedValue = $this->convertCollectionTypeToDatabaseValue($fieldName, $value);
                    break;
                case Type::DEFAULT_TYPE:
                    $convertedValue = $value;
                    break;
            }
        } else {
            $convertedValue = $value;
        }

        return $convertedValue;
    }

    protected function convertCollectionTypeToPHPValue($fieldName, $rows) {
        $oldMapping = $this->typesMapping;
        $internalCollection = $this->dataSourceConfig->getInternalCollectionForField($fieldName);
        $this->typesMapping = $internalCollection->getTypesMapping();
        $newRows = array();
        foreach($rows as $row) {
            $newRow = array();
            foreach ($row as $fieldName => $value) {
                $newRow[$fieldName] = $this->convertToPHPValue($fieldName, $value);
            }
            $newRows[] = $newRow;
        }

        $this->typesMapping = $oldMapping;
        return $newRows;
    }

    protected function convertToPHPValue($fieldName, $value) {
        $convertedValue = null;
        if (isset($this->typesMapping[$fieldName])) {
            switch($this->typesMapping[$fieldName]) {
                case Type::MONGO_ID:
                    $convertedValue = Type::getType(Type::MONGO_ID)->convertToPHPValue($value);
                    break;
                case Type::MONGO_DATE:
                    $convertedValue = Type::getType(Type::MONGO_DATE)->convertToPHPValue($value);
                    break;
                case Type::COLLECTION:
                    $convertedValue = $this->convertCollectionTypeToPHPValue($fieldName, $value);
                    break;
                case Type::DEFAULT_TYPE:
                    $convertedValue = $value;
                    break;
            }
        } else {
            $convertedValue = $value;
        }

        return $convertedValue;
    }

    protected function parseToDatabaseCondition($condition) {
        if (is_array($condition['value'])) {
            $ids = array();
            foreach ($condition['value'] as $value)
                $ids[] = $this->convertToDatabaseValue($condition['columnName'], $value);
            return $ids;
        }
        return $this->convertToDatabaseValue($condition['columnName'], $condition['value']);
    }

    protected function getPrimaryKeyValueFromRow($row) {
        $primaryKeys = array();

        foreach ($this->localPrimaryKeys as $value) {
            $primaryKeys[$value] = (String) $row[$value];
        }

        return $primaryKeys;
    }

    protected function persistRows(DataSet $dataSet) {
        $persistedRows = array();
        foreach ($dataSet->getRows() as $key => $row) {
            $persistedRows[$key] = $this->convertRowToPHPValue($this->persistRow($row));
        }
        return $persistedRows;
    }

    protected function convertRowToDatabaseValue($row) {
        $convertedRow = array();

        foreach ($row as $field => $value) {
            $convertedRow[$field] = $this->convertToDatabaseValue($field, $value);
        }

        return $convertedRow;
    }

    protected function convertRowToPHPValue($row) {
        $convertedRow = array();

        foreach ($row as $field => $value) {
            $convertedRow[$field] = $this->convertToPHPValue($field, $value);
        }

        return $convertedRow;
    }

    protected function processCriteriaForWrapperByRow($primaryKeys, $row) {
        $oldMapping = $this->typesMapping;
        $this->typesMapping = $this->wrapperCollection->getTypesMapping();

        $criteria = array();
        foreach ($primaryKeys as $key => $value) {
            $criteria[$key] = $this->convertToDatabaseValue($key, $row[$value]);
        }

        $this->typesMapping = $oldMapping;
        return $criteria;
    }

    protected function persistOnInternalCollection($row, $isNew) {
        $internalCollectionOptions = $this->wrapperCollection->getInternalCollectionOptions($this->tableName);
        $primaryKeys = $internalCollectionOptions['primaryKeysMapping'];

        $tableName = $this->wrapperCollection->getTableName();
        $fieldName = $internalCollectionOptions['fieldName'];
        $criteria = $this->processCriteriaForWrapperByRow($primaryKeys, $row);

        if (!$isNew) {
            $update = array('$pull' => array($fieldName => array('_id' => $row['_id'])));
            $this->mongo->update($tableName, $criteria, $update);
        }

        $row = $this->filterDataSourceColumns($this->dataSourceConfig->getColumns(), $row);
        $update = array('$push' => array($fieldName => $row));

        $this->mongo->update($tableName, $criteria, $update);
    }

    protected function addSequentialKeysValues($row) {
        $sequentialColumn = $this->dataSourceConfig->getSequentialColumn();

        if ($sequentialColumn != null && !isset($row[$sequentialColumn])) {
            $row[$sequentialColumn] = new \MongoDB\BSON\ObjectId();
        }

        $oldDataSourceConfig = $this->dataSourceConfig;
        foreach ($oldDataSourceConfig->getInternalCollections() as $internalCollection) {
            $internalCollectionOptions = $oldDataSourceConfig->getInternalCollectionOptions($internalCollection->getName());
            $this->dataSourceConfig = $internalCollection;

            $row[$internalCollectionOptions['fieldName']] = array_map(function($row) {
                return $this->addSequentialKeysValues($row);
            }, $row[$internalCollectionOptions['fieldName']]);
        }
        $this->dataSourceConfig = $oldDataSourceConfig;

        return $row;
    }

    protected function persistRow($row) {
        $row = $this->convertRowToDatabaseValue($row);

        $isNew = $this->isNew($row);

        if ($isNew) {
            $row = $this->addSequentialKeysValues($row);
        }

        if ($this->wrapperCollection) {
            $this->persistOnInternalCollection($row, $isNew);
        } else {
            $row = $this->filterDataSourceColumns($this->dataSourceConfig->getColumns(), $row);
            if ($isNew) {
                $this->mongo->insert($this->tableName, $row);
            } else {
                $criteria = $this->buildCriteriaFromRow($row);
                $updateQuery = $this->createUpdateQuery($row);
                $this->mongo->update($this->tableName, $criteria, $updateQuery);
            }
        }

        return $row;
    }

    protected function createUpdateQuery($row) {
        $row = array_filter($row, function($field) {
            return !(isset($this->typesMapping[$field]) && $this->typesMapping[$field] === Type::COLLECTION);
        }, ARRAY_FILTER_USE_KEY);
        return array(
            '$set' => $row
        );
    }

    protected function isNew($row) {
        return $row['__is_new'];
    }

    protected function processFilterConditions(FilterCriteria $filterCriteria) {
        $expressionBuilder = new Expr();
        foreach ($filterCriteria->getConditions() as $condition) {
            $conditionValue = $this->parseToDatabaseCondition($condition);
            switch ($condition['operator']) {
                case FilterCriteria::EQ:
                    $expressionBuilder->field($condition['columnName'])->equals($conditionValue);
                    break;
                case FilterCriteria::IN:
                    $expressionBuilder->field($condition['columnName'])->in($conditionValue);
                    break;
                case FilterCriteria::NOT_IN:
                    $expressionBuilder->field($condition['columnName'])->nin($conditionValue);
                    break;
                case FilterCriteria::LT:
                    $expressionBuilder->field($condition['columnName'])->lt($conditionValue);
                    break;
                case FilterCriteria::LTE:
                    $expressionBuilder->field($condition['columnName'])->lte($conditionValue);
                    break;
                case FilterCriteria::GTE:
                    $expressionBuilder->field($condition['columnName'])->gte($conditionValue);
                    break;
                case FilterCriteria::GT:
                    $expressionBuilder->field($condition['columnName'])->gt($conditionValue);
                    break;
                case FilterCriteria::BETWEEN:
                    list($start, $end) = $conditionValue;
                    $expressionBuilder->field($condition['columnName'])->range($start, $end);
                    break;
            }
        }
        return $expressionBuilder->getQuery();
    }

    protected function filterDataSourceColumns($columns, $row) {
        $newRow = array();

        foreach ($columns as $column) {
            if (isset($this->typesMapping[$column]) && $this->typesMapping[$column] === Type::COLLECTION) {
                $oldMapping = $this->typesMapping;
                $internalCollection = $this->dataSourceConfig->getInternalCollectionForField($column);
                $this->typesMapping = $internalCollection->getTypesMapping();

                $internalCollectionColumns = $internalCollection->getColumns();

                $newRow[$column] = array_map(function($row) use ($internalCollectionColumns) {
                    return $this->filterDataSourceColumns($internalCollectionColumns, $row);
                }, $row[$column]);

                $this->typesMapping = $oldMapping;
            } else {
                $newRow[$column] = $row[$column];
            }
        }

        return $newRow;
    }

    protected function filterColumnsForResultSet($row) {
        $columnsForResultSet = $this->dataSourceConfig->getColumnsForResultSet();
        $rowFiltered = array();
        if (count($columnsForResultSet) > 0) {
            foreach ($columnsForResultSet as $columnName) {
                if (array_key_exists($columnName, $row)) {
                    $rowFiltered[$columnName] = $row[$columnName];
                }
            }
        }
        return $rowFiltered;
    }

    protected function prepareRowsForResultSet($rows) {
        $preparedRows = array();
        foreach ($rows as $row) {
            $row = $this->convertRowToPHPValue($row);
            $row = $this->filterColumnsForResultSet($row);
            $row['__is_new'] = false;
            $preparedRows[] = $row;
        }

        return $preparedRows;
    }

    protected function createFilterCriteriaForWrapper(FilterCriteria $filterCriteria, array $primaryKeys) {
        $conditions = $filterCriteria->getConditions();

        $invertedPrimaryKeys = array();
        foreach ($primaryKeys as $key => $value) {
            $invertedPrimaryKeys[$value] = $key;
        }

        $newConditions = array();
        foreach ($conditions as $condition) {
            if (isset($invertedPrimaryKeys[$condition['columnName']])) {
                $condition['columnName'] = $invertedPrimaryKeys[$condition['columnName']];
                $newConditions[] = $condition;
            }
        }

        return new FilterCriteria(
            $filterCriteria->getDataSourceName(),
            $newConditions,
            $filterCriteria->getPage(),
            $filterCriteria->getPageSize()
        );
    }

    protected function createFilterCriteriaForInternalCollection(FilterCriteria $filterCriteria, array $primaryKeys) {
        $conditions = $filterCriteria->getConditions();

        $invertedPrimaryKeys = array();
        foreach ($primaryKeys as $key => $value) {
            $invertedPrimaryKeys[$value] = $key;
        }

        $newConditions = array();
        foreach ($conditions as $condition) {
            if (!isset($invertedPrimaryKeys[$condition['columnName']])) {
                $newConditions[] = $condition;
            }
        }

        return new FilterCriteria(
            $filterCriteria->getDataSourceName(),
            $newConditions,
            $filterCriteria->getPage(),
            $filterCriteria->getPageSize()
        );
    }

    protected function filterRowsWithFilterCriteria(FilterCriteria $filterCriteria, array $rows) {
        $conditions = $filterCriteria->getConditions();
        return array_values(array_filter($rows, function($row) use ($conditions) {
            foreach ($conditions as $condition) {
                if ($row[$condition['columnName']] !== $condition['value']) {
                    return false;
                }
            }
            return true;
        }));
    }

    protected function retrieveRowsOnInternalCollection(FilterCriteria $filterCriteria) {
        $internalCollectionOptions = $this->wrapperCollection->getInternalCollectionOptions($this->tableName);
        $primaryKeys = $internalCollectionOptions['primaryKeysMapping'];

        $wrapperFilterCriteria = $this->createFilterCriteriaForWrapper($filterCriteria, $primaryKeys);
        $criteria = $this->processFilterConditions($wrapperFilterCriteria);

        $tableName = $this->wrapperCollection->getTableName();
        $services = $this->mongo->find($tableName, $criteria);
        if (count($services) === 1) {
            $internalCriteria = $this->createFilterCriteriaForInternalCollection($filterCriteria, $primaryKeys);
            $rows = $this->filterRowsWithFilterCriteria($internalCriteria, $services[0][$internalCollectionOptions['fieldName']]->bsonSerialize());
        } else {
            $rows = array();
        }

        return $rows;
    }

    protected function retrieveRows(FilterCriteria $filterCriteria) {
        if ($this->wrapperCollection) {
            $rows = $this->retrieveRowsOnInternalCollection($filterCriteria);
        } else {
            $conditions = $this->processFilterConditions($filterCriteria);
            $rows = $this->mongo->find($this->tableName, $conditions);
        }
        $rows = $this->prepareRowsForResultSet($rows);

        return $rows;
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