<?php
namespace Zeedhi\Framework\DataSource\Manager\Doctrine;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\ORM\DateTime;

/**
 * Class ManagerImpl
 *
 * A implementation of DataSourceManager that will use Doctrine internally.
 * Once it use Doctrine objects, all your rules and validations will be applied to all managed data.
 *
 * @package Zeedhi\Framework\DataSource\Manager\Doctrine
 */
class ManagerImpl extends Manager\AbstractManager implements Manager {

    /** @var string @todo this should be externalized */
    protected $dateTimeFormat = "d/m/Y H:i:s";
    /** @var EntityManager The Doctrine\ORM\EntityManager used to communicate with Doctrine. */
    protected $entityManager;

    /**
     * Constructor...
     *
     * @param EntityManager $entityManager The instance of EntityManager.
     * @param NameProvider  $nameProvider  The NameProvider used to discover ClassNames.
     * @param ParameterBag  $parameterBag  The bag of parameters used in queries.
     */
    public function __construct(EntityManager $entityManager, NameProvider $nameProvider, ParameterBag $parameterBag) {
        $this->entityManager = $entityManager;
        parent::__construct($nameProvider, $parameterBag);
    }

    /**
     * @inheritdodc
     */
    protected function persistRow($row) {
        $entity = $this->findOrNew($row);
        $rowFiltered = $this->filterColumnsByRealColumns($row);
        $this->setFieldValues($entity, $rowFiltered);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        foreach($this->entityToRow($entity) as $column => $value) {
            $row[$column] = $value;
        }
    }

    protected function beginTransaction() {
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function commit() {
        $this->entityManager->getConnection()->commit();
    }

    protected function rollback() {
        $this->entityManager->getConnection()->rollBack();
    }

    /**
     * @param $row
     * @return object
     */
    protected function deleteRow($row) {
        $entity = $this->findEntity($this->getCurrentClassName(), $row);
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * Convert a entity into a plan array, row, indexed by column names..
     *
     * @param object $entity The entity to be converted into a row.
     *
     * @return array
     */
    protected function entityToRow($entity) {
        $classMetadata = $this->entityManager->getClassMetadata(get_class($entity));
        $platform = $this->entityManager->getConnection()->getDatabasePlatform();
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entityRow = array();
        $originalEntityData = $unitOfWork->getOriginalEntityData($entity);
        foreach($originalEntityData as $field => $value) {
            if($classMetadata->hasField($field)) {
                $fieldMapping = $classMetadata->getFieldMapping($field);
                $columnName = $fieldMapping['columnName'];
                if ($value instanceof DateTime) {
                    $value = $value->format($this->dateTimeFormat);
                } else {
                    $type = $fieldMapping['type'];
                    $value = Type::getType($type)->convertToDatabaseValue($value, $platform);
                }

                $entityRow[$columnName] = $value;
            } else if($classMetadata->hasAssociation($field)) {
                $association = $classMetadata->getAssociationMapping($field);
                if ($association['isOwningSide']) {
                    $value = $value ? $unitOfWork->getEntityIdentifier($value) : null;
                    $targetClass = $this->entityManager->getClassMetadata($association['targetEntity']);
                    foreach ($association['sourceToTargetKeyColumns'] as $sourceColumn => $targetColumn) {
                        $targetFieldForColumn = $targetClass->getFieldForColumn($targetColumn);
                        $targetFieldMapping = $targetClass->getFieldMapping($targetFieldForColumn);
                        $type = $targetFieldMapping['type'];
                        $entityRow[$sourceColumn] = Type::getType($type)->convertToDatabaseValue($value[$targetFieldForColumn], $platform);
                    }
                }
            }
        }

        return $entityRow;
    }

    /**
     * Convert a bunch of entities into a bunch plan arrays, rows, and each row indexed by column names.
     *
     * @param object[] $entities A bunch entities to be converted.
     *
     * @return array
     */
    protected function entitiesToRow($entities) {
        $entitiesRows = array();
        foreach ($entities as $entity) {
            $entitiesRows[] = $this->entityToRow($entity);
        }

        return $entitiesRows;
    }

    /**
     * Discover it's need to create a new instance of find a already existent of that row.
     *
     * @param array  $row            The row to be created a entity.
     *
     * @return object The correspondent entity.
     */
    protected function findOrNew($row) {
        $className = $this->getCurrentClassName();
        if ($row["__is_new"]) {
            $entity = new $className();
        } else {
            $entity = $this->findEntity($className, $row);
        }

        return $entity;
    }

    /**
     * Populate a entity with row data.
     *
     * @param object $entity The object to be populate with data.
     * @param array  $row    The data to populate the object.
     *
     * @return void
     */
    protected function setFieldValues($entity, $row) {
        $className = get_class($entity);
        $classMetaData = $this->entityManager->getClassMetadata($className);
        $platform = $this->entityManager->getConnection()->getDatabasePlatform();
        foreach ($row as $columnName => $columnValue) {
            if ($columnName === '__is_new') continue;
            $propertyName = $classMetaData->getFieldForColumn($columnName);
            if ($classMetaData->hasAssociation($propertyName)) {
                $associationMapping = $classMetaData->getAssociationMapping($propertyName);
                $associatedClassName = $associationMapping['targetEntity'];
                if($columnValue === null) {
                    $classMetaData->setFieldValue($entity, $propertyName, $columnValue);
                } else {
                    $associatedObject = $this->entityManager->find($associatedClassName, $columnValue);
                    $classMetaData->setFieldValue($entity, $propertyName, $associatedObject);
                }
            } else if ($classMetaData->hasField($propertyName)) {
                $fieldMapping = $classMetaData->getFieldMapping($propertyName);
                $fieldType = Type::getType($fieldMapping['type']);
                if ($fieldType->getName() == Type::DATETIME || $fieldType->getName() == Type::DATE) {
                    $fieldValue = $columnValue ? DateTime::createFromFormat($this->dateTimeFormat, $columnValue) : null;
                } else {
                    $fieldValue = $fieldType->convertToPHPValue($columnValue, $platform);
                }

                $classMetaData->setFieldValue($entity, $propertyName, $fieldValue);
            }
        }
    }

    /**
     * Retrieve a existent entity of the given row.
     *
     * @param string $className The ClassName of the wanted entity.
     * @param array  $row       The row data of wanted entity.
     *
     * @return object
     */
    protected function findEntity($className, $row) {
        $classMetaData = $this->entityManager->getClassMetadata($className);
        $id = array();
        foreach ($classMetaData->getIdentifierColumnNames() as $idColumnName) {
            $id[$classMetaData->getFieldForColumn($idColumnName)] = $row[$idColumnName];
        }

        //@todo throw a exception when record is not found.
        return $this->entityManager->find($className, $id);
    }

    /**
     * Set the format of data time columns.
     *
     * @param string $dateTimeFormat Format.
     *
     * @return void
     */
    public function setDateTimeFormat($dateTimeFormat) {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * @param $row
     * @param $columns
     * @return array
     * @throws Exception
     */
    protected function filterRowByColumns($row, $columns) {
        $rowFiltered = array();
        foreach ($columns as $columnName) {
            // isset($row[$columnName]) cant be used because return false if value is null.
            if (array_key_exists($columnName, $row)) {
                $rowFiltered[$columnName] = $row[$columnName];
            } else {
                throw Exception::columnNotPresentInResultSet($columnName, $this->dataSourceConfig->getName());
            }
        }

        return $rowFiltered;
    }

    /**
     * Remove column not present in data source.
     *
     * @param array $row
     *
     * @return array
     *
     * @throws Exception
     */
    protected function filterColumnsByRealColumns($row) {
        $columns = array_filter($this->dataSourceConfig->getColumns(), function($column) { return !is_null($column);});
        return $this->filterRowByColumns($row, $columns);
    }

    /**
     * @param $row
     * @return array
     * @throws Exception
     */
    protected function filterColumnsForResultSet($row) {
        return $this->filterRowByColumns($row, $this->dataSourceConfig->getColumnsForResultSet());
    }

    /**
     * Retrieve entities that match with given criteria.
     *
     * @param FilterCriteria $filterCriteria The criteria.
     *
     * @return object[]
     */
    protected function retrieveEntities(FilterCriteria $filterCriteria) {
        $className = $this->getCurrentClassName();
        try {
            $classMetaData = $this->entityManager->getClassMetadata($className);
        } catch(\Exception $e){
            throw Exception::errorLoadingMetadataForClass($e->getMessage(), $className);
        }
        $alias = $tableName = $classMetaData->getTableName();
        $connection = $this->entityManager->getConnection();

        $query = $connection->createQueryBuilder()->select("{$alias}.*")->from($tableName, $alias);
        $params = $this->processFilterConditions($filterCriteria, $query);

        $resultSetMapping = new ResultSetMappingBuilder($this->entityManager);
        $resultSetMapping->addRootEntityFromClassMetadata($className, $alias);

        return $this->entityManager->createNativeQuery($query->getSQL(), $resultSetMapping)->execute($params);
    }

    /**
     * @return string
     */
    protected function getCurrentClassName() {
        return $this->nameProvider->getClassName($this->dataSourceConfig->getTableName());
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @return array
     */
    protected function retrieveRows(FilterCriteria $filterCriteria) {
        if ($this->dataSourceConfig->hasQuery()) {
            $rows = $this->retrieveRowsByQuery($filterCriteria);
        } else {
            $rows = $this->retrieveRowsByEntities($filterCriteria);
        }

        $preparedRows = $this->prepareRowsForResultSet($rows);
        return $preparedRows;
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @return array
     */
    protected function retrieveRowsByQuery(FilterCriteria $filterCriteria) {
        $query = $this->entityManager->getConnection()->createQueryBuilder()
            ->select($this->dataSourceConfig->getColumnsForResultSet())
            ->from('(' . $this->dataSourceConfig->getQuery() . ') ZEEDHI_ALIAS');
        $params = $this->processFilterConditions($filterCriteria, $query);
        $types = $this->inferTypes($params);
        $query->setParameters($params, $types);
        $rows = $query->execute()->fetchAll();
        return $rows;
    }

    /**
     * @param FilterCriteria $filterCriteria
     * @return array
     */
    protected function retrieveRowsByEntities(FilterCriteria $filterCriteria) {
        $entities = $this->retrieveEntities($filterCriteria);
        $rows = $this->entitiesToRow($entities);
        return $rows;
    }

    /**
     * @param $rows
     * @return array
     * @throws Exception
     */
    protected function prepareRowsForResultSet($rows) {
        $preparedRows = array();
        foreach ($rows as $row) {
            $preparedRows[] = $this->filterColumnsForResultSet($row);
        }

        return $preparedRows;
    }
}