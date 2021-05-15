<?php
namespace Zeedhi\Framework\DataSource\Manager\CustomColumn;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DataSource\AssociatedWithDataSource;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;

class ManagerImpl implements Manager {

    /** @var NameProvider */
    protected $nameProvider;
    /** @var Manager */
    protected $originalManager;
    /** @var Manager */
    protected $managerForCustomColumns;

    /** @var Configuration */
    protected $dataSourceConfig;
    /** @var string */
    protected $dataSourceName;
    /** @var string */
    protected $tableName;
    /** @var array */
    protected $primaryKeys;
    /** @var array */
    protected $columns;
    /** @var array */
    protected $customColumns;

    const ALL_DATA = "__ALL";

    /**
     * __construct
     *
     * @param NameProvider $nameProvider            Provider for data source configuration
     * @param Manager      $originalManager         Manager used to save original datasource
     * @param Manager      $managerForCustomColumns Manager used to save custom columns values
     */
    public function __construct(NameProvider $nameProvider, Manager $originalManager, Manager $managerForCustomColumns) {
        $this->nameProvider            = $nameProvider;
        $this->originalManager         = $originalManager;
        $this->managerForCustomColumns = $managerForCustomColumns;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(DataSet $dataSet) {
        $this->loadCurrentDataSource($dataSet);

        $originalDataSet = $this->removeCustomColumnsFromDataSet($dataSet);
        $persistedRows = $this->originalManager->persist($originalDataSet);

        $dataSet = $this->addPrimaryKeysToDataSet($dataSet, $persistedRows);

        $customColumnDataSet = $this->factoryDataSetForPersist($dataSet);
        $this->managerForCustomColumns->persist($customColumnDataSet);

        return $persistedRows;
    }

    protected function addPrimaryKeysToDataSet($dataSet, $originalResult) {
        $rows = array_map(function($row, $newRow) {
            return $this->addPrimaryKeysToRow($row, $newRow);
        }, $dataSet->getRows(), $originalResult);
        return new DataSet($dataSet->getDataSourceName(), $rows);
    }

    protected function addPrimaryKeysToRow($row, $newRow) {
        foreach ($this->getPrimaryKeys($newRow) as $key => $value) {
            $row[$key] = $value;
        }

        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DataSet $dataSet) {
        $this->loadCurrentDataSource($dataSet);

        $originalDataSet = $this->removeCustomColumnsFromDataSet($dataSet);
        $deletedRows = $this->originalManager->delete($originalDataSet);

        $primaryKeys = $this->convertPrimaryKeys($dataSet->getRows());

        $customColumnDataSet = $this->factoryDataSetForDelete($primaryKeys);
        $this->managerForCustomColumns->delete($customColumnDataSet);

        return $deletedRows;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(FilterCriteria $filterCriteria) {
        $this->loadCurrentDataSource($filterCriteria);

        $originalResult = $this->originalManager->findBy($filterCriteria);

        $filterCriteria = $this->factoryFilterCriteria($originalResult);
        $customColumnRows = $this->managerForCustomColumns->findBy($filterCriteria);

        return $this->buildResultDataSet($originalResult, $customColumnRows->getRows());
    }

    protected function factoryDataSetForPersist(DataSet $dataSet) {
        $rows = array();

        foreach ($dataSet->getRows() as $row) {
            $primaryKey = $this->convertPrimaryKey($row);

            foreach ($this->customColumns as $customColumn) {
                $rows[] = array(
                    '__is_new'       => $row['__is_new'],
                    'dataSourceName' => $this->dataSourceName,
                    'key'            => $primaryKey,
                    'columnName'     => $customColumn,
                    'value'          => $row[$customColumn]
                );
            }
        }

        return new DataSet('customColumn', $rows);
    }

    protected function buildResultDataSet(DataSet $originalResult, array $customColumnRows) {
        $customColumnsValues = array();

        foreach ($customColumnRows as $row) {
            $customColumnsValues[$row['key']][$row['columnName']] = $row['value'];
        }

        $rows = array_map(function($row) use ($customColumnsValues) {
            if (isset($customColumnsValues[$this->convertPrimaryKey($row)])) {
                $rowCustomValues = $customColumnsValues[$this->convertPrimaryKey($row)];

                foreach ($rowCustomValues as $key => $value) {
                    $row[$key] = $value;
                }
            }

            return $row;
        }, $originalResult->getRows());
        return new DataSet($this->dataSourceName, $rows);
    }

    protected function factoryFilterCriteria(DataSet $dataSet) {
        $primaryKeys = $this->convertPrimaryKeys($dataSet->getRows());

        $filter = new FilterCriteria('customColumn');
        $filter->addCondition('dataSourceName', FilterCriteria::EQ, $this->dataSourceName);
        $filter->addCondition('key', FilterCriteria::IN, $primaryKeys);

        return $filter;
    }

    protected function factoryDataSetForDelete(array $primaryKeys) {
        $newRows = array();

        foreach ($primaryKeys as $primaryKey) {
            foreach ($this->customColumns as $customColumn) {
                $newRows[] = array(
                    'dataSourceName' => $this->dataSourceName,
                    'key'            => $primaryKey,
                    'columnName'     => $customColumn
                );
            }
        }

        return new DataSet('customColumn', $newRows);
    }

    protected function convertPrimaryKeys(array $rows) {
        return array_map(function($row) {
            return $this->convertPrimaryKey($row);
        }, $rows);
    }

    protected function convertPrimaryKey($row) {
        return json_encode($this->getPrimaryKeys($row));
    }

    protected function getPrimaryKeys($row) {
        $newRow = new Row();

        foreach ($this->primaryKeys as $key) {
            $newRow[$key] = $row[$key];
        }

        return $newRow;
    }

    protected function removeCustomColumnsFromDataSet(DataSet $dataSet) {
        return new DataSet(
            $this->dataSourceName,
            $this->removeCustomColumnsFromRows($dataSet->getRows())
        );
    }

    protected function removeCustomColumnsFromRows(array $rows) {
        return array_map(function($row) {
            return $this->removeCustomColumnsFromRow($row);
        }, $rows);
    }

    protected function removeCustomColumnsFromRow($row) {
        $newRow = new Row();

        foreach ($row as $key => $value) {
            if (!in_array($key, $this->customColumns)) {
                $newRow[$key] = $value;
            }
        }

        return $newRow;
    }

    protected function loadCurrentDataSource(AssociatedWithDataSource $associatedWithDataSource) {
        $this->dataSourceConfig = $this->nameProvider->getDataSourceByName($associatedWithDataSource->getDataSourceName());
        $this->dataSourceName   = $associatedWithDataSource->getDataSourceName();
        $this->tableName        = $this->dataSourceConfig->getTableName();
        $this->primaryKeys      = $this->dataSourceConfig->getPrimaryKeyColumns();
        $this->columns          = $this->dataSourceConfig->getColumns();
        $this->customColumns    = $this->dataSourceConfig->getCustomColumns();
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