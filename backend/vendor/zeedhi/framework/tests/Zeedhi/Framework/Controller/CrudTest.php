<?php
namespace tests\Zeedhi\Framework\Controller;

use Doctrine\ORM\EntityManager;
use HumanRelation\Entities\Regions;
use HumanRelation\Util\DataSource\NameProvider;
use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager\Doctrine\ManagerImpl;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Routing\Router;

class CrudTest extends \PHPUnit\Framework\TestCase {

    const USER_ID = 'USR_ORG_20';
    /** @var ManagerImpl */
    protected $doctrineDataSourceManager;
    /** @var EntityManager */
    protected $entityManager;
    /** @var CrudImpl */
    protected $crudController;

    public function setUp() {
        /** @var EntityManager $entityManager */
        $entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
        $this->entityManager = $entityManager;
        $parameterBag = new ParameterBag(new ArrayImpl());
        $this->doctrineDataSourceManager = new ManagerImpl($entityManager, new NameProvider(), $parameterBag);
        $this->createSavePoint();
        $this->crudController = new CrudImpl($this->doctrineDataSourceManager);
    }

    protected function tearDown() {
        $this->backToSavePoint();
        parent::tearDown();
    }

    protected function createSavePoint() {
        $this->entityManager->beginTransaction();
    }

    protected function backToSavePoint() {
        $this->entityManager->rollback();
    }

    public function testSave() {
        $row = array(
            "__is_new"    => true,
            "REGION_ID"   => 6,
            "REGION_NAME" => "Antarctica"
        );
        $dataSet = new DataSet("regions", array($row));
        $request = new Request\DataSet($dataSet, Router::METHOD_POST, "/regions/save", self::USER_ID);
        $response = new Response();
        $this->crudController->save($request, $response);
        /** @var DataSet $returnedDataSet */
        $returnedDataSet = current($response->getDataSets());
        $this->assertCount(1, $returnedDataSet->getRows());
        $responseNotifications = $response->getNotifications();
        $this->assertCount(1, $responseNotifications, "Should return 1 message.");
        $this->assertNotification("1 row(s) persisted with success", current($responseNotifications));
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 6 must exist.");
        $this->assertEquals("Antarctica", $region->getRegionName(), "Name of region with id 6 must be 'Antarctica'.");
    }

    public function testDelete() {
        $row = array(
            "__is_new"     => false,
            "COUNTRY_ID"   => "AR",
            "COUNTRY_NAME" => "Argentina",
            "REGION_ID"    => 2
        );
        $dataSet = new DataSet("countries", array($row));
        $request = new Request\DataSet($dataSet, Router::METHOD_POST, "/countries/delete", self::USER_ID);
        $response = new Response();
        $this->crudController->setDataSourceName('countries');
        $this->crudController->delete($request, $response);
        /** @var DataSet $returnedDataSet */
        $returnedDataSet = current($response->getDataSets());
        $this->assertCount(1, $returnedDataSet->getRows());
        $responseNotifications = $response->getNotifications();
        $this->assertCount(1, $responseNotifications, "Should return 1 message.");
        $this->assertNotification("1 row(s) deleted.", current($responseNotifications));
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AT");
        $this->assertNull($region, "Region 'Austrália' must not exists anymore.");
    }

    public function testFind() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition("REGION_NAME", "Americas");
        $request = new Request\Filter($filterCriteria, Router::METHOD_POST, "/regions/find", self::USER_ID);
        $response = new Response();
        $this->crudController->find($request, $response);
        /** @var DataSet[] $dataSets */
        $dataSets = $response->getDataSets();
        $this->assertCount(1, $dataSets, "Should return 1 dataset.");

        /** @var $dataSet DataSet */
        $dataSet = current($dataSets);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Must return 1 rows.");
        foreach($dataSet->getRows() as $row) {
            $this->assertEquals("Americas", $row["REGION_NAME"], "All retrieved regions should name 'Americas'.");
        }
    }

    /**
     * @param string           $expectedMessage
     * @param Response\Message $message
     */
    protected function assertMessage($expectedMessage, $message) {
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Message', $message, "Message in response must be a instance of DTO\\Response\\Message.");
        $this->assertEquals($expectedMessage, $message->getMessage(), "Message should be expected.");
    }

    /**
     * @param string                $expectedMessage
     * @param Response\Notification $notification
     */
    protected function assertNotification($expectedMessage, $notification) {
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Notification', $notification, "Notification in response must be a instance of DTO\\Response\\Notification.");
        $this->assertEquals($expectedMessage, $notification->getMessage(), "Message should be expected.");
    }

    public function testSaveError() {
        $row = array(
            "__is_new"    => true,
            "REGION_ID"   => 6,// region_name size will explode column length
            "REGION_NAME" => "Antarctica Antarctica Antarctica Antarctica Antarctica Antarctica Antarctica Antarctica"
        );
        $dataSet = new DataSet("regions", array($row));
        $request = new Request\DataSet($dataSet, Router::METHOD_POST, "/regions/save", self::USER_ID);
        $response = new Response();
        $this->crudController->save($request, $response);
        /** @var DataSet $returnedDataSet */
        $this->assertCount(0, $response->getDataSets(), "No data sets must be returned");
        $responseMessages = $response->getMessages();
        $this->assertCount(0, $responseMessages, "No success message should be returned.");
        $error = $response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $error);
        $this->assertNotEmpty($error->getMessage(), 'Error message should not be empty!');
        $this->assertNotEmpty($error->getStackTrace(), 'Stack trace should not be empty!');
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
        $this->assertNull($region, "Region must not be persisted");
    }

    public function testDeleteError() {
        $row = array(
            "__is_new"     => false,
            "REGION_ID"    => 2,
            "REGION_NAME"  => "Americas",
        );
        $dataSet = new DataSet("regions", array($row));
        $request = new Request\DataSet($dataSet, Router::METHOD_POST, "/regions/delete", self::USER_ID);
        $response = new Response();
        $this->crudController->setDataSourceName('regions');
        $this->crudController->delete($request, $response);
        $this->assertCount(0, $response->getDataSets(), "No datasets should exist");
        $this->assertCount(0, $response->getMessages(), "No success mesage should be returned");
        $error = $response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $error);
        $this->assertNotEmpty($error->getMessage(), 'Error message should not be empty!');
        $this->assertNotEmpty($error->getStackTrace(), 'Stack trace should not be empty!');
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 2);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "If delete error Region must exists.");
    }

}
