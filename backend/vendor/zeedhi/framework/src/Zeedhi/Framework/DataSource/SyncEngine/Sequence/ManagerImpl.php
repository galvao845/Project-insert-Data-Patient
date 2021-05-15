<?php
namespace Zeedhi\Framework\DataSource\SyncEngine\Sequence;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\Kernel;

class ManagerImpl extends Manager\Doctrine\ManagerImpl implements Manager {

	/** @var \Zeedhi\Framework\Kernel */
	protected $kernel;
	/** @var SyncEngineImpl */
	protected $syncEngine;
	/** @var array[] */
	protected $originalRows = array();

	/**
	 * Construct...
	 *
	 * @param EntityManager                 $entityManager
	 * @param SyncEngineImpl                $syncEngine
	 * @param Kernel                        $kernel
	 * @param Manager\Doctrine\NameProvider $nameProvider
     * @param ParameterBag                  $parameterBag
	 */
	public function __construct(
		EntityManager $entityManager,
		SyncEngineImpl $syncEngine,
		Kernel $kernel,
		Manager\Doctrine\NameProvider $nameProvider,
		ParameterBag $parameterBag
	) {
		$this->syncEngine = $syncEngine;
		$this->kernel = $kernel;
		parent::__construct($entityManager, $nameProvider, $parameterBag);
	}

	/**
	 * {@inheritdoc}
	 */
	public function persist(DataSet $dataSet) {
		$this->loadCurrentDataSource($dataSet);
		$syncDataSet = $this->synchronizeDataSet($dataSet);
        $doctrinePersistedRows = $this->doctrinePersist($syncDataSet);
        return $this->syncEngine->unSyncPkRows($doctrinePersistedRows, $this->dataSourceConfig, $dataSet->getRows());
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(DataSet $dataSet) {
		$this->loadCurrentDataSource($dataSet);
		$syncDataSet = $this->synchronizeDataSet($dataSet);
        $doctrineDeletedRows = $this->doctrineDelete($syncDataSet);
        return $this->syncEngine->unSyncPkRows($doctrineDeletedRows, $this->dataSourceConfig, $dataSet->getRows());
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
		foreach($dataSet->getRows() as $key => $row) {
			$this->originalRows[$key] = $row instanceof Row ? $row->getArrayCopy() : $row;
		}

		$syncRows = $this->syncEngine->synchronizeRows($this->dataSourceConfig, $this->originalRows, $this->getUserId());
		$syncDataSet = new DataSet($dataSet->getDataSourceName(), $syncRows);
		return $syncDataSet;
	}

	/**
	 * Call a flush in entity manager wrapped by transaction control.
	 *
	 * @throws \Doctrine\ORM\OptimisticLockException See \Doctrine\ORM\EntityManager::flush doc.
	 * @throws \Exception                            See \Doctrine\ORM\UnitOfWork::commit doc.
	 */
	protected function flushWithTransactionControl() {
		$connection = $this->entityManager->getConnection();
		$connection->beginTransaction();
		try {
			$this->entityManager->flush();
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollBack();
			throw Manager\Exception::errorExecutingQuery($e);
		}
	}

	/**
	 * @param DataSet $dataSet
	 *
	 * @return array Rows with primary key columns values.
	 *
	 * @throws \Exception
	 */
	protected function doctrinePersist(DataSet $dataSet) {
		$persistedRows = array();
		foreach ($dataSet->getRows() as $key => $row) {
	        $entity = $this->findOrNew($row);
	        $rowFiltered = $this->filterColumnsByRealColumns($row);
	        $this->setFieldValues($entity, $rowFiltered);
	        $this->entityManager->persist($entity);
	        $this->entityManager->flush();
			$this->postStoreId($key, $entity);
			$persistedRows[$key] = $this->getPrimaryKeyValueFromRow($row);
		}

		$this->flushWithTransactionControl();
		return $persistedRows;
	}

	protected function doctrineDelete(DataSet $dataSet) {
		$deletedRows = array();
		foreach ($dataSet->getRows() as $key => $row) {
			// New rows doesn't need to be deleted, since their do not exist.
			if ($row['__is_new'] === false) {
				try {
					$this->deleteRow($row);
					$deletedRows[$key] = $this->getPrimaryKeyValueFromRow($row);
				} catch (ORMInvalidArgumentException $e) {
					// When row already deleted, not returned id.
				}
			}
		}

		$this->flushWithTransactionControl();
		return $deletedRows;
	}

	/**
	 * @param $rowKey
	 * @param $entity
	 */
	protected function postStoreId($rowKey, $entity) {
		$originalRow = $this->originalRows[$rowKey];
		$this->syncEngine->postStoreId($this->dataSourceConfig, $originalRow, $this->getUserId(), $entity->getId());
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
     * @param DataSet $dataSet
     *
     * @return array with unsynchronized rows.
     */
    protected function unSynchronizeRows($dataSet) {
        $dataSourceName = $dataSet->getDataSourceName();
        $configuration = $this->nameProvider->getDataSourceByName($dataSourceName);
        return $this->syncEngine->unSynchronizeRows($configuration, $dataSet->getRows(), $this->getUserId());
    }

    public function findBy(FilterCriteria $filterCriteria) {
        $syncFilterCriteria = $this->syncFilterCriteria($filterCriteria);
        $dataSet = parent::findBy($syncFilterCriteria);
        $unSyncRows = $this->unSynchronizeRows($dataSet);
        return new DataSet($dataSet->getDataSourceName(), $unSyncRows);
    }
} 