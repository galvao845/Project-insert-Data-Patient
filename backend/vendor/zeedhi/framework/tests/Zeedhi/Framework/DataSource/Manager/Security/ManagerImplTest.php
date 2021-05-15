<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Security;

use HumanRelation\Entities\Regions;
use HumanRelation\Util\DataSource\NameProvider;

use Doctrine\ORM\EntityManager;

use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Row;

use Zeedhi\Framework\DataSource\Manager\SQL;
use Zeedhi\Framework\DataSource\Manager\Security\ManagerImpl;


class ManagerImplTest extends \PHPUnit\Framework\TestCase {

    /** @var EntityManager */
    protected $entityManager;
    /** @var Manager */
    protected $securityManager;
    protected $parameterBag;
    protected $connection;
    protected $nameProvider;

    public function setUp() {
        $this->entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();

        $this->nameProvider = new NameProvider();
        $this->parameterBag = new ParameterBag(new ArrayImpl());
        $this->connection = $this->entityManager->getConnection();

        $sqlManager = new SQL\ManagerImpl($this->connection, $this->nameProvider, $this->parameterBag);

        $this->securityManager = new ManagerImpl($sqlManager);

        $this->createSavePoint();
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

    public function testPersist(){
        $region_id = "5";
        $region_name = "Oceania";

        $data = array(
            new Row(array(
                "REGION_ID"   => $region_id,
                "REGION_NAME" => $region_name,
                '__is_new'    => true
            )));

        $expectedValue = array('REGION_ID' => $region_id);
        $persistDataSet = new DataSet("regions", $data);
        $result = $this->securityManager->persist($persistDataSet);

        $this->assertContains($expectedValue, $result);
    }

    public function testRemove(){
        $region_id = "5";
        $region_name = "Oceania";
        $data = array(
            new Row(array(
                "REGION_ID"   => $region_id,
                "REGION_NAME" => $region_name,
                '__is_new'    => true
            )));
        $persistDataSet = new DataSet("regions", $data);
        $this->securityManager->persist($persistDataSet);

        $removedData = array(
            new Row(array(
                "REGION_ID"   => $region_id,
                '__is_new'    => false
            )));
        $expectedValue = array('REGION_ID' => $region_id);
        $removeDataSet  = new DataSet("regions", $removedData);

        $result = $this->securityManager->delete($removeDataSet);

        $this->assertContains($expectedValue, $result);
    }

    public function testFindByEqualsTo(){
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition("REGION_NAME", "Americas");
        $dataSet = $this->securityManager->findBy($filterCriteria);

        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");
        $row = $dataSet->getRows()[0];
        $this->assertEquals("Americas", $row["REGION_NAME"], "The retrieved region should name 'Americas'.");
    }

    public function testSQLInjection(){
        $SQLInject = "REGION_NAME = 'Americas' OR 1=1 UNION SELECT 1, FIRST_NAME FROM EMPLOYEES \n--\\";

        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition($SQLInject, FilterCriteria::IS_NULL, null);
        $dataSet = $this->securityManager->findBy($filterCriteria);

        $this->assertEmpty($dataSet->getRows());
    }

    public function testFindByLikeAllWithNamedColumns(){
        $filterCriteria = new FilterCriteria("locations");
        $filterCriteria->addCondition("STATE_PROVINCE|CITY", FilterCriteria::LIKE_ALL, "%Tokyo%");
        $dataSet = $this->securityManager->findBy($filterCriteria);

        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");

        $row = $dataSet->getRows()[0];
        $this->assertEquals("Tokyo Prefecture", $row["STATE_PROVINCE"], "The result should be 'Tokyo Prefecture' on column STATE_PROVINCE.");
    }

    public function testFindByLikeAll(){
        $filterCriteria = new FilterCriteria("locations");
        $filterCriteria->addCondition("*", FilterCriteria::LIKE_ALL, "%Tokyo%");
        $dataSet = $this->securityManager->findBy($filterCriteria);

        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");

        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");

        $row = $dataSet->getRows()[0];
        $this->assertEquals("Tokyo Prefecture", $row["STATE_PROVINCE"], "The result should be 'Tokyo Prefecture' on column STATE_PROVINCE.");
    }

    public function testSQLInjectionWithLikeAllOperator(){
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition('REGION_ID', 1);
        $filterCriteria->addCondition("REGION_ID|REGION_NAME|'a') = 'a' OR LOWER(REGION_NAME", FilterCriteria::LIKE_ALL, '%ope%');
        $dataSet = $this->securityManager->findBy($filterCriteria);

        $this->assertEmpty($dataSet->getRows());
    }

    public function testInjectionByOperator() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition('REGION_ID', "= REGION_ID UNION SELECT EMPLOYEE_ID, FIRST_NAME FROM EMPLOYEES WHERE 1 =", 1);
        $dataSet = $this->securityManager->findBy($filterCriteria);
        $this->assertEmpty($dataSet->getRows());
    }

    public function testOrderBy(){
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition('REGION_NAME', FilterCriteria::LIKE, "%ca%");
        $filterCriteria->addOrderBy('REGION_NAME');
        $dataSet = $this->securityManager->findBy($filterCriteria);

        $row = $dataSet->getRows()[0];
        $this->assertEquals('Americas', $row["REGION_NAME"], "The first result should be 'Americas'");

        $row = $dataSet->getRows()[1];
        $this->assertEquals('Middle East and Africa', $row["REGION_NAME"], "The first result should be 'Middle East and Africa'");
    }

    public function testOrderByInjection(){
        $this->markTestSkipped('Problem not fixed');

        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addOrderBy('REGION_ID DESC --', 'ASC');

        $dataSet = $this->securityManager->findBy($filterCriteria);

        $rows = $dataSet->getRows();
        $this->assertCount(4, $rows, 'With injection, the manager should not select a row.');
    }

    public function testGroupBy(){
        $filterCriteria = new FilterCriteria("countries_with_id_counted");
        $filterCriteria->addCondition('REGION_ID', 1);
        $filterCriteria->addGroupBy('REGION_ID');

        $dataSet = $this->securityManager->findBy($filterCriteria);

        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");

        $row = $dataSet->getRows()[0];
        $this->assertEquals($row['COUNT(COUNTRY_ID)'], 8, "Should group '8' countries with region_is equals to '1'");
    }

    public function testGroupByInjection(){

        $filterCriteria = new FilterCriteria("employees");
        $filterCriteria->addCondition('SALARY', FilterCriteria::GTE, 17000);
        $filterCriteria->addGroupBy('JOB_ID, REGION_NAME UNION SELECT EMPLOYEE_ID, FIRST_NAME FROM EMPLOYEES');

        $dataSet = $this->securityManager->findBy($filterCriteria);
        $this->assertEmpty($dataSet->getRows(), "With injection, the manager should not return anything.");
    }

    public function testPopulateDataSet(){
        $dataSet = new DataSet('simple_regions', array(
            new Row(array('column' => array('__ALL' => [])))
        ));

        $expectedResult = new DataSet('simple_regions', array(
            new Row(array(
                'column' => array(
                    array('REGION_ID' => 1, 'REGION_NAME' => 'Europe', '__is_new' => false),
                    array('REGION_ID' => 2, 'REGION_NAME' => 'Americas', '__is_new' => false),
                    array('REGION_ID' => 3, 'REGION_NAME' => 'Asia', '__is_new' => false),
                    array('REGION_ID' => 4, 'REGION_NAME' => 'Middle East and Africa', '__is_new' => false)
                )
            ))
        ));

        $finalResult = $this->securityManager->populateDataSet($dataSet);

        $this->assertEquals($expectedResult, $finalResult);
    }

    public function testBuildAllDataFilter(){
        $value = array('__ALL' => array(array('name' => 'NAME', 'operator' => '=', 'value' => 'Europe')));
        $rows = array(array('column_EXCEPT' => array(4), 'column' => $value));
        $expectedFilterCriteria = new FilterCriteria('simple_regions');
        $expectedFilterCriteria->addCondition('column', "NOT_IN", array(4));
        $expectedFilterCriteria->addCondition('NAME', '=', 'Europe');
        $finalResult = $this->securityManager->buildAllDataFilter($rows, $value, 'column', 'simple_regions');

        $this->assertEquals($expectedFilterCriteria, $finalResult);
    }
}
