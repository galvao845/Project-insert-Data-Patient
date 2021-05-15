<?php
namespace tests\Zeedhi\Framework\Controller;

use Doctrine\ORM\EntityManager;
use HumanRelation\Util\DataSource\NameProvider;
use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager\Doctrine\ManagerImpl;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Routing\Router;

class CrudWithParameterBagTest extends \PHPUnit\Framework\TestCase {

    CONST USER_ID = 'kqq5qys4m9rudi';

    /** @var CrudWithParameterBagImpl */
    protected $crudController;
    /** @var EntityManager */
    protected $entityManager;
    /** @var ParameterBag */
    protected $parameterBag;

    protected function createSavePoint() {
        $this->entityManager->beginTransaction();
    }

    protected function backToSavePoint() {
        $this->entityManager->rollback();
    }

    protected function setUp() {
        $this->entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
        $this->parameterBag = new ParameterBag(new ArrayImpl());
        $doctrineDataSourceManager = new ManagerImpl($this->entityManager, new NameProvider(), $this->parameterBag);
        $this->crudController = new CrudWithParameterBagImpl(
            $doctrineDataSourceManager,
            $this->parameterBag
        );

        $this->createSavePoint();
    }

    protected function tearDown() {
        $this->backToSavePoint();
        parent::tearDown();
    }

    public function testFindWithParameterBag() {
        $filterCriteria = new FilterCriteria("locations_by_region");
        $filterCriteria->addCondition("REGION_ID", "1");
        $request = new Request\Filter($filterCriteria, Router::METHOD_POST, "/employees_by_job/find", self::USER_ID);
        $response = new Response();
        $this->crudController->find($request, $response);
        /** @var DataSet $dataSets */
        $dataSets = $response->getDataSets();
        $this->assertCount(1, $dataSets, "Should return 1 dataset.");

        /** @var $dataSet DataSet */
        $dataSet = current($dataSets);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(9, $dataSet->getRows(), "Must return 9 rows.");

        $this->assertEquals("1", $this->parameterBag->get('REGION_ID'), "The bag must contain parameter value");
    }

    public function testFindWithParameterBagWithNormalConditions() {
        $filterCriteria = new FilterCriteria("locations_by_region");
        $filterCriteria->addCondition("REGION_ID", "1");
        $filterCriteria->addCondition("CITY", FilterCriteria::LIKE, "G%");
        $request = new Request\Filter($filterCriteria, Router::METHOD_POST, "/employees_by_job/find", self::USER_ID);
        $response = new Response();
        $this->crudController->find($request, $response);
        /** @var DataSet $dataSets */
        $dataSets = $response->getDataSets();
        $this->assertCount(1, $dataSets, "Should return 1 dataset.");

        /** @var $dataSet DataSet */
        $dataSet = current($dataSets);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Must return 1 row.");

        $this->assertEquals("1", $this->parameterBag->get('REGION_ID'), "The bag must contain parameter value");
    }
}
