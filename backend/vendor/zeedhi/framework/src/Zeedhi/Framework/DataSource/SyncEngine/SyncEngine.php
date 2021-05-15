<?php
namespace Zeedhi\Framework\DataSource\SyncEngine;

use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\Cache\Exception;
use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

abstract class SyncEngine {

    /** @var Cache */
    protected $cache;

    /**
     * Constructor...
     *
     * @param Cache $cache The cache to map 'local' to 'server' id.
     */
    public function __construct(Cache $cache) {
        $this->cache = $cache;
    }

    /**
     * Generate a server id for given row.
     *
     * @param Configuration $configuration The metadata about row to be sync.
     * @param array         $row           The row to be sync.
     * @param string|int    $userId        The user session identifier.
     *
     * @return mixed
     */
    abstract protected function getNewServerId(Configuration $configuration, $row, $userId);

    /**
     * Search for a id for row hash in cache.
     *
     * @param string $rowHash The row hash.
     *
     * @return string|int
     */
    protected function findStoredId($rowHash) {
        return $this->cache->fetch($rowHash);
    }

    /**
     * Return a server id for given row.
     *
     * @param Configuration $configuration The metadata about row to be sync.
     * @param array         $row           The row to be sync.
     * @param string|int    $userId        The user session identifier.
     *
     * @return int|string
     */
    protected function retrieveServerId(Configuration $configuration, $row, $userId) {
        $rowHash = $this->getRowHash($configuration, $row, $userId);
        try {
            $serverId = $this->findStoredId($rowHash);
        } catch (Exception $e) {
            $serverId = $this->getNewServerId($configuration, $row, $userId);
            $this->storeId($rowHash, $serverId);
        }

        return $serverId;
    }

    /**
     * Store id in our cached, index by row hash.
     * Return true if success, false otherwise.
     *
     * @param string     $rowHash  The row rash.
     * @param string|int $serverId The server id.
     *
     * @return bool
     */
    protected function storeId($rowHash, $serverId) {
        return $this->cache->save($rowHash, $serverId);
    }

    public function unSynchronizeRow(Configuration $configuration, $row, $userId) {
        if ($sequentialColumn = $configuration->getSequentialColumn()) {
            try {
                $rowHash = $this->getRowHash($configuration, $row, $userId);
                $localId = $this->findStoredId($rowHash);
                $row[$sequentialColumn] = $localId;
            } catch (Exception $e) {
                //nothing to do, the row just was not synchronized.
            }
        }

        foreach($configuration->getRelations() as $relationMetaData) {
            if ($relationMetaData['targetSequentialColumn']) {
                try {
                    $relationRow = array();
                    foreach($relationMetaData['localColumns'] as $key => $localColumn) {
                        $targetColumn = $relationMetaData['targetColumns'][$key];
                        $relationRow[$targetColumn] = $row[$localColumn];
                    }

                    $relationConfiguration = $this->factoryConfigurationFromRelationMetadata($relationMetaData);
                    $rowHash = $this->getRowHash($relationConfiguration, $relationRow, $userId);
                    $localId = $this->findStoredId($rowHash);
                    $row[$relationMetaData['localSequentialColumn']] = $localId;
                } catch (Exception $e) {
                    //nothing to do, the relation was not synchronized.
                }
            }
        }

        return $row;
    }

    /**
     * Unsynchronized given rows with proper client ids.
     *
     * @param Configuration $configuration The metadata about row to be unsync.
     * @param array         $rows          A array of rows to be unsynchronized.
     * @param string|int    $userId        The user session identifier.
     *
     * @return array
     */
    public function unSynchronizeRows(Configuration $configuration, $rows, $userId) {
        $unSynchronizeRows = array();
        foreach($rows as $key => $row) {
            $unSynchronizeRows[$key] = $this->unSynchronizeRow($configuration, $row, $userId);
        }

        return $unSynchronizeRows;
    }

    /**
     * Synchronize given row with proper servers ids.
     *
     * @param Configuration $configuration The metadata about row to be sync.
     * @param array         $row           The row to be sync.
     * @param string|int    $userId        The user session identifier.
     *
     * @return array
     */
    public function synchronizeRow(Configuration $configuration, $row, $userId) {
        if ($sequentialColumn = $configuration->getSequentialColumn()) {
            if (isset($row[$sequentialColumn])) {
                if (is_array($row[$sequentialColumn])) {
                    foreach($row[$sequentialColumn] as $key => $value) {
                        if (is_string($value) && substr($value, 0, 7) === "unsync_") {
                            $auxRow = $row;
                            $unSyncId = $auxRow[$sequentialColumn] = $value;
                            $auxRow[$sequentialColumn] = $this->retrieveServerId($configuration, $auxRow, $userId);
                            $this->storeId($this->getRowHash($configuration, $auxRow, $userId), $unSyncId);
                            $row[$sequentialColumn][$key] = $auxRow[$sequentialColumn];
                        }
                    }
                } else if (substr($row[$sequentialColumn], 0, 7) === "unsync_") {
                    $unSyncId = $row[$sequentialColumn];
                    $row[$sequentialColumn] = $this->retrieveServerId($configuration, $row, $userId);
                    $this->storeId($this->getRowHash($configuration, $row, $userId), $unSyncId);
                }
            }
        }

        foreach($configuration->getRelations() as $relationMetaData) {
            $localSequentialColumn = $relationMetaData['localSequentialColumn'];
            if (isset($row[$localSequentialColumn])) {
                if (is_array($row[$localSequentialColumn])) {
                    foreach($row[$localSequentialColumn] as $key => $value) {
                        if (is_string($value) && substr($value, 0, 7) === "unsync_") {
                            $localRow = $row;
                            $unSyncId = $localRow[$localSequentialColumn] = $value;
                            $localRow[$localSequentialColumn] = $this->retrieveRelationServerId($localRow, $userId, $relationMetaData);
                            $relationConfiguration = $this->factoryConfigurationFromRelationMetadata($relationMetaData);
                            $relationRow = $this->buildRelationRow($localRow, $relationMetaData);
                            $this->storeId($this->getRowHash($relationConfiguration, $relationRow, $userId), $unSyncId);
                            $row[$localSequentialColumn][$key] = $localRow[$localSequentialColumn];
                        }
                    }
                } else if (is_string($row[$localSequentialColumn]) && substr($row[$localSequentialColumn], 0, 7) === "unsync_") {
                    $unSyncId = $row[$localSequentialColumn];
                    $row[$localSequentialColumn] = $this->retrieveRelationServerId($row, $userId, $relationMetaData);
                    $relationConfiguration = $this->factoryConfigurationFromRelationMetadata($relationMetaData);
                    $relationRow = $this->buildRelationRow($row, $relationMetaData);
                    $this->storeId($this->getRowHash($relationConfiguration, $relationRow, $userId), $unSyncId);
                }
            }

        }

        return $row;
    }

    /**
     * Synchronize given rows with proper servers ids.
     *
     * @param Configuration $configuration The metadata about row to be sync.
     * @param array         $rows          A array of rows to be synchronized.
     * @param string|int    $userId        The user session identifier.
     *
     * @return array
     */
    public function synchronizeRows(Configuration $configuration, $rows, $userId) {
        $synchronizedRows = array();
        foreach($rows as $row) {
            $synchronizedRows[] = $this->synchronizeRow($configuration, $row, $userId);
        }

        return $synchronizedRows;
    }

    /**
     * Create a row hash for given row and user.
     *
     * @param Configuration $configuration The metadata about row to be sync.
     * @param array         $row           The row to be sync.
     * @param string|int    $userId        The user session identifier.
     *
     * @return string
     */
    protected function getRowHash(Configuration $configuration, $row, $userId) {
        $rowHash = array(
            "TABLE_NAME" => $configuration->getTableName(),
            "USER_ID" => $userId
        );

        foreach ($configuration->getPrimaryKeyColumns() as $primaryKeyColumnName) {
            $rowHash[$primaryKeyColumnName] = $row[$primaryKeyColumnName];
        }

        $rowHash = implode("#", $rowHash);
        return $rowHash;
    }

    /**
     * @param $row
     * @param $userId
     * @param $relationMetaData
     * @return array
     */
    protected function retrieveRelationServerId($row, $userId, $relationMetaData) {
        $relationConfiguration = $this->factoryConfigurationFromRelationMetadata($relationMetaData);
        $relationRow = $this->buildRelationRow($row, $relationMetaData);
        return $this->retrieveServerId($relationConfiguration, $relationRow, $userId);
    }

    /**
     * @param array         $persistedRows
     * @param Configuration $configuration
     * @param array         $originalRows
     *
     * @return array
     */
    public function unSyncPkRows($persistedRows, $configuration, $originalRows) {
        $sequentialColumn = $configuration->getSequentialColumn();
        if ($sequentialColumn) {
            $unSyncDataSetRows = $originalRows;
            $unSyncPersistedRows = array();
            foreach ($persistedRows as $key => $persistedRow) {
                $unSyncDataSetRow = $unSyncDataSetRows[$key];
                $persistedRow[$sequentialColumn] = $unSyncDataSetRow[$sequentialColumn];
                $unSyncPersistedRows[$key] = $persistedRow;
            }
        } else {
            $unSyncPersistedRows = $persistedRows;
        }

        return $unSyncPersistedRows;
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @return FilterCriteria
     */
    public function syncFilterCriteria(FilterCriteria $filterCriteria, Configuration $config, $userId) {
        $conditions = $filterCriteria->getConditions();
        $syncConditionsArray = array();
        foreach ($conditions as $condition) {
            $syncConditionsArray[$condition['columnName']] = $condition['value'];
        }

        $syncConditionsArray = $this->synchronizeRow($config, $syncConditionsArray, $userId);
        $syncFilterCriteria = new FilterCriteria($filterCriteria->getDataSourceName());
        $syncFilterCriteria->setPage($filterCriteria->getPage());
        $syncFilterCriteria->setPageSize($filterCriteria->getPageSize());
        $syncFilterCriteria->setWhereClause($filterCriteria->getWhereClause(), $filterCriteria->getWhereClauseParams());
        foreach ($conditions as $condition) {
            $syncFilterCriteria->addCondition(
                $condition['columnName'],
                $condition['operator'],
                $syncConditionsArray[$condition['columnName']]
            );
        }

        foreach($filterCriteria->getOrderBy() as $column => $value) {
            $syncFilterCriteria->addOrderBy($column, $value);
        }

        return $syncFilterCriteria;
    }

    /**
     * @param $row
     * @param $relationMetaData
     * @return array
     */
    protected function buildRelationRow($row, $relationMetaData) {
        $relationRow = array();
        foreach ($relationMetaData['targetColumns'] as $key => $targetColumn) {
            $relationRow[$targetColumn] = $row[$relationMetaData['localColumns'][$key]];
        }
        return $relationRow;
    }

    /**
     * @param $relationMetaData
     * @return Configuration
     */
    protected function factoryConfigurationFromRelationMetadata($relationMetaData) {
        return Configuration::factoryFromRelation($relationMetaData);
    }
}