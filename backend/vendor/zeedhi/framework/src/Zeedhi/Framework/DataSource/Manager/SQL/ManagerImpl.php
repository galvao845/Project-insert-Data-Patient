<?php
namespace Zeedhi\Framework\DataSource\Manager\SQL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\DBAL\Query\QueryBuilder;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\ParameterBag;

class ManagerImpl extends Manager\AbstractManager implements Manager {

    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection, Manager\Doctrine\NameProvider $nameProvider, ParameterBag $parameterBag) {
        $this->connection = $connection;
        parent::__construct($nameProvider, $parameterBag);
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder() {
        return $this->connection->createQueryBuilder();
    }

    /**
     * @param $row
     * @return array
     */
    protected function buildRowToPersist($row) {
        $rowToPersist = array();
        $columns = $this->dataSourceConfig->getColumns();
        foreach ($row as $column => $value) {
            if (in_array($column, $columns)) {
                $rowToPersist[$column] = $value;
            }
        }
        return $rowToPersist;
    }

    /**
     * @return QueryBuilder
     */
    protected function createInsertQuery() {
        $columns = $this->dataSourceConfig->getColumns();
        $values = array();
        foreach ($columns as $column) {
            if ($column != null) {
                $values[$column] = ':' . $column;
            }
        }

        $insert = $this->createQueryBuilder()->insert($this->dataSourceConfig->getTableName())->values($values);
        return $insert;
    }

    /**
     * @return QueryBuilder
     */
    protected function createDeleteQuery() {
        $delete = $this->createQueryBuilder()->delete($this->dataSourceConfig->getTableName());
        $this->buildConditionsForPkColumns($delete);
        return $delete;
    }

    /**
     * @param QueryBuilder $query
     */
    protected function buildConditionsForPkColumns(QueryBuilder $query) {
        $pkColumns = $this->dataSourceConfig->getPrimaryKeyColumns();
        foreach ($pkColumns as $pkColumn) {
            $query->andWhere("{$pkColumn} = :{$pkColumn}");
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function createUpdateQuery() {
        $update = $this->createQueryBuilder()->update($this->dataSourceConfig->getTableName());
        $this->buildConditionsForPkColumns($update);

        $pkColumns = $this->dataSourceConfig->getPrimaryKeyColumns();
        foreach ($this->dataSourceConfig->getColumns() as $column) {
            if($column !== null && !in_array($column, $pkColumns)) {
                $update->set($column, ':'.$column);
            }
        }

        return $update;
    }

    /**
     * @param QueryBuilder $query
     * @param array        $rowToPersist
     * @return mixed
     */
    protected function executeQuery($query, $rowToPersist) {
        $query->setParameters($rowToPersist);
        return $query->execute();
    }

    /**
     * @param $row
     */
    protected function deleteRow($row) {
        $delete = $this->createDeleteQuery();
        $delete->setParameters((array)$row)->execute();
    }

    /**
     * @return QueryBuilder
     */
    protected function createSelectQuery() {
        $select = $this->createQueryBuilder()->select($this->dataSourceConfig->getColumnsForResultSet());
        if($this->dataSourceConfig->hasQuery()) {
            $from = "(".$this->dataSourceConfig->getQuery().") ZEEDHI_ALIAS";
        } else {
            $from = $this->dataSourceConfig->getTableName();
        }
        $select->from($from);
        return $select;
    }

    /**
     * @param InvalidFieldNameException $e
     *
     * @return Exception
     */
    protected function rethrowException(InvalidFieldNameException $e) {
        $matches = array();
        preg_match(": \"[A-Za-z_]+\":", $e->getPrevious()->getMessage(), $matches);
        $column = trim(current($matches), "\" ");
        return Exception::columnNotPresentInResultSet($column, $this->dataSourceConfig->getName(), $e);
    }

    /**
     * @param FilterCriteria $filterCriteria
     *
     * @return array
     *
     * @throws
     */
    protected function retrieveRows(FilterCriteria $filterCriteria) {
        try {
            $query = $this->createSelectQuery();

            $params = $this->processFilterConditions($filterCriteria, $query);

            $types = $this->inferTypes($params);
            $query->setParameters($params, $types);

            $rows = $query->execute()->fetchAll();

            return $rows;
        } catch (InvalidFieldNameException  $e) {
            throw $this->rethrowException($e);
        }
    }

    protected function beginTransaction() {
        $this->connection->beginTransaction();
    }

    protected function commit() {
        $this->connection->commit();
    }

    protected function rollback() {
        $this->connection->rollBack();
    }

    protected function persistRow($row) {
        $rowToPersist = $this->buildRowToPersist($row);
        $query = $row['__is_new'] ? $this->createInsertQuery() : $this->createUpdateQuery();
        $this->executeQuery($query, $rowToPersist);
    }
}