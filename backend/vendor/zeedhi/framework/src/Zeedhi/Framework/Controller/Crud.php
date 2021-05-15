<?php
namespace Zeedhi\Framework\Controller;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DTO;
use Zeedhi\Framework\DTO\Response\Notification;
use Zeedhi\Framework\DataSource\Manager;

/**
 * Class Crud
 *
 * Class to be extended by controllers that want have CRUD capabilities by using entity manager and DataSet and FilterRequests.
 *
 * @package Zeedhi\Framework\Controller
 */
abstract class Crud extends Simple {

    /** @var Manager The Entity Manager used to interact with Database. */
    protected $dataSourceManager;
    /** @var Manager The Entity Manager used to interact with Database. */
    protected $dataSourceName;

    /**
     * Constructor...
     *
     * @param Manager $dataSourceManager     The DataSourceManager implementation used by your product.
     */
    public function __construct(Manager $dataSourceManager) {
        $this->dataSourceManager = $dataSourceManager;
    }


    /**
     * Action used for insert new rows and update the old ones.
     *
     * @param DTO\Request\DataSet $request  A DTO\Request\DataSet with all rows to be persisted..
     * @param DTO\Response        $response A DTO\Response, mostly of cases will only add messages.
     *
     * @return void
     */
    public function save(DTO\Request\DataSet $request, DTO\Response $response) {
        try {
            $dataSet = $request->getDataSet();
            $dataSet->setDataSourceName($this->dataSourceName);
            $dataSet = $this->dataSourceManager->populateDataSet($dataSet);
            $dataSet = $this->prepareDataSet($dataSet);
            $persistedRows = $this->dataSourceManager->persist($dataSet);
            $response->addDataSet(new DataSet($this->dataSourceName, $persistedRows));
            $nrPersistedRows = count($persistedRows);
            $response->addNotification(new Notification("{$nrPersistedRows} row(s) persisted with success", Notification::TYPE_SUCCESS));
        } catch (\Exception $e) {
            $response->setCriticalError(new DTO\Response\Error($e->getMessage(), $e->getCode(), $e->getTraceAsString()));
        }
    }

    /**
     * Action used for find rows that match with given FilterCriteria
     *
     * @param DTO\Request\Filter $request  A DTO\Request with FilterCriteria to match the rows in DataSourceManager.
     * @param DTO\Response       $response A DTO\Response to be added with DataSets of matched rows.
     *
     * @return void
     */
    public function find(DTO\Request\Filter $request, DTO\Response $response) {
        $filterData = $request->getFilterCriteria();
        $filterData->setDataSourceName($this->dataSourceName);
        $dataSet = $this->dataSourceManager->findBy($filterData);
        $response->addDataSet($dataSet);
    }

    /**
     * Action used for delete some rows.
     *
     * @param DTO\Request\DataSet $request  A DTO\Request with DataSet of all rows to be deleted..
     * @param DTO\Response        $response A DTO\Response, mostly of cases will only add messages.
     *
     * @return void
     */
    public function delete(DTO\Request\DataSet $request, DTO\Response $response) {
        try {
            $dataSet = $request->getDataSet();
            $dataSet->setDataSourceName($this->dataSourceName);
            $dataSet = $this->dataSourceManager->populateDataSet($dataSet);
            $dataSet = $this->prepareDataSet($dataSet);
            $deletedRows = $this->dataSourceManager->delete($dataSet);
            $response->addDataSet(new DataSet($this->dataSourceName, $deletedRows));
            $nrDeletedRows = count($deletedRows);
            $response->addNotification(new Notification("{$nrDeletedRows} row(s) deleted.", Notification::TYPE_SUCCESS));
        } catch (\Exception $e) {
            $response->setCriticalError(new DTO\Response\Error($e->getMessage(), $e->getCode(), $e->getTraceAsString()));
        }
    }

    /**
     * @param DTO\Request\DataSet $dataSet
     *
     * @return DTO\Request\DataSet
     */
    public function prepareDataSet($dataSet){
        return $dataSet;
    }
}