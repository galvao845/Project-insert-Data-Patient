<?php

use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\DataSet;
use HumanRelation\Entities\Regions;
use HumanRelation\Entities\Countries;
use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Row;

class ManagerImplTest extends PHPUnit\Framework\TestCase {

    /** @var Manager\DataColumnToColumnConversion\ManagerImpl */
    protected $dataSourceManager;
    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    public function setUp() {
        $entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
        $nameProvider = new \HumanRelation\Util\DataSource\NameProvider();
        $parameterBag = new ParameterBag(new ArrayImpl());
        $doctrineManager = new Manager\Doctrine\ManagerImpl($entityManager, $nameProvider, $parameterBag);
        $this->dataSourceManager = new Manager\DataColumnToColumnConversion\ManagerImpl($doctrineManager, $nameProvider);
        $this->entityManager = $entityManager;
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

    public function testFindByWithChangedDataColumnNames() {
        $filterCriteria = new FilterCriteria("simple_regions");
        $filterCriteria->addCondition("NAME", "Americas");
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");
        foreach($dataSet->getRows() as $row) {
            $this->assertEquals("Americas", $row["NAME"], "All retrieved regions should name 'America'.");
        }
    }

    public function testPersistInsertWithChangedDataColumnNames() {
        $row = array(
            "__is_new"    => true,
            "ID"   => 6,
            "NAME" => "Antarctica"
        );
        $dataSet = new DataSet("simple_regions", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 6 must exist.");
        $this->assertEquals("Antarctica", $region->getRegionName(), "Name of region with id 6 must be 'Antarctica'.");
    }

    public function testPersistUpdateWithChangedDataColumnNames() {
        $row = array(
            "__is_new"    => false,
            "ID"   => 4,
            "NAME" => "Africa"
        );
        $dataSet = new DataSet("simple_regions", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 4);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 4 must exist.");
        $this->assertEquals("Africa", $region->getRegionName(), "Name of region with id 4 must be 'Africa'.");
    }

    public function testDeleteWithChangedDataColumnNames() {
        $row = array(
            "__is_new" => false,
            "ID"   => "AR",
            "NAME" => "Argentina",
            "REGION_ID" => 2
        );
        $dataSet = new DataSet("simple_countries", array($row));
        $deletedRows = $this->dataSourceManager->delete($dataSet);
        $this->assertCount(1, $deletedRows, "Number of deleted rows must be 1.");
        /** @var Countries $country */
        $country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
        $this->assertNull($country, "Country 'Argentina' must not exists anymore.");
    }

    public function testFindByWithOperatorInWithChangedDataColumnNames() {
        $filterCriteria = new FilterCriteria("simple_regions");
        $filterCriteria->addCondition("ID", FilterCriteria::IN, array(1, 2));
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(2, $dataSet->getRows(), "Should return 2 rows.");
    }

    public function testFindByWithOperatorNotInWithChangedDataColumnNames() {
        $filterCriteria = new FilterCriteria("simple_regions");
        $filterCriteria->addCondition("ID", FilterCriteria::NOT_IN, array(1, 2));
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(2, $dataSet->getRows(), "Should return 2 rows");
    }

    public function testFullSearchWithChangedDataColumnNames() {
        // Australia e United States.
        $expectedCountryIds = array('AU', 'US');
        $filterCriteria = new FilterCriteria("simple_countries");
        $filterCriteria->addCondition("*", FilterCriteria::LIKE_ALL, '%US%');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(2, $rows);
        foreach($rows as $row){
            $this->assertContains($row['ID'], $expectedCountryIds);
        }
    }

    public function testFullSearchWithColumnListWithChangedDataColumnNames() {
        // Australia e United States.
        $expectedCountryIds = array('AU', 'US');
        $filterCriteria = new FilterCriteria("simple_countries");
        $filterCriteria->addCondition("ID|NAME", FilterCriteria::LIKE_ALL, '%US%');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(2, $rows);
        foreach($rows as $row){
            $this->assertContains($row['ID'], $expectedCountryIds);
        }
    }

    public function testFindAllInQueryNewDataSourceConfigFormat() {
        $filterCriteria = new FilterCriteria("full_countries_with_regions");
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(25, $rows);
    }

    public function testPersistWithQueryDataSource() {
        $rows = array(
            array('COUNTRY_ID' => 'BR', 'COUNTRY_NAME' => 'Brazil', 'REGION_ID' => 2, 'REGION_NAME' => 'Americas', '__is_new' => false)
        );
        $persistedRows = $this->dataSourceManager->persist(new DataSet("full_countries_with_regions", $rows));
        $this->assertCount(1, $persistedRows);
        /** @var Countries $country */
        $country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", 'BR');
        $this->assertEquals('Brazil', $country->getCountryName());
    }

    public function testFindAllWithQueryAndFilterConditionsOnQueryDataColumns() {
        $expectedCountryIds = array('US', 'CA', 'BR', 'MX', 'AR');
        $filterCriteria = new FilterCriteria("full_countries_with_regions");
        $filterCriteria->addCondition("REGION_NAME", FilterCriteria::LIKE, 'Americas');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(5, $rows);
        foreach($rows as $row) {
            $this->assertContains($row['COUNTRY_ID'], $expectedCountryIds);
        }
    }

    public function testFindAllWithQueryAndLikeAll() {
        // Australia e United States.
        $expectedCountryIds = array('AU', 'US');
        $filterCriteria = new FilterCriteria("full_countries_with_regions");
        $filterCriteria->addCondition("*", FilterCriteria::LIKE_ALL, '%US%');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(2, $rows);
        foreach($rows as $row) {
            $this->assertContains($row['COUNTRY_ID'], $expectedCountryIds);
        }
    }

    public function testPreserveOrderBy() {
        $managerInterfaceClassName = '\Zeedhi\Framework\DataSource\Manager';
        $managerMock = $this->getMockBuilder($managerInterfaceClassName)
            ->setMethods(get_class_methods($managerInterfaceClassName))
            ->getMock();

        $managerMock->expects($this->once())
            ->method('findBy')
            ->with($this->callback(function(FilterCriteria $receivedFilterCriteria) {
                    return $receivedFilterCriteria->getOrderBy() == array("COUNTRY_NAME"=> "ASC");
            }))
            ->willReturn(new DataSet("full_countries_with_regions", array()));

        $dataColumnConversionManager = new Manager\DataColumnToColumnConversion\ManagerImpl(
            $managerMock,
            new \HumanRelation\Util\DataSource\NameProvider()
        );

        $filterCriteria = new FilterCriteria("full_countries_with_regions");
        $filterCriteria->addOrderBy("COUNTRY_NAME", "ASC");
        $dataSet = $dataColumnConversionManager->findBy($filterCriteria);
        $this->assertEquals("full_countries_with_regions", $dataSet->getDataSourceName());
        $this->assertCount(0, $dataSet->getRows());
    }

    public function testPopulateDataSet(){
        $dataSet = new DataSet('simple_regions', array(
            new Row(array('column' => array('__ALL' => [])))
        ));

        $expectedResult = new DataSet('simple_regions', array(
            new Row(array(
                'column' => array(
                    array('ID' => 1, 'NAME' => 'Europe', '__is_new' => false),
                    array('ID' => 2, 'NAME' => 'Americas', '__is_new' => false),
                    array('ID' => 3, 'NAME' => 'Asia', '__is_new' => false),
                    array('ID' => 4, 'NAME' => 'Middle East and Africa', '__is_new' => false)
                )
            ))
        ));

        $finalResult = $this->dataSourceManager->populateDataSet($dataSet);

        $this->assertEquals($expectedResult, $finalResult);
    }

    public function testBuildAllDataFilter(){
        $value = array('__ALL' => array(array('name' => 'NAME', 'operator' => '=', 'value' => 'Europe')));
        $rows = array(array('column_EXCEPT' => array(4), 'column' => $value));
        $expectedFilterCriteria = new FilterCriteria('simple_regions');
        $expectedFilterCriteria->addCondition('column', "NOT_IN", array(4));
        $expectedFilterCriteria->addCondition('NAME', '=', 'Europe');
        $finalResult = $this->dataSourceManager->buildAllDataFilter($rows, $value, 'column', 'simple_regions');

        $this->assertEquals($expectedFilterCriteria, $finalResult);
    }
}
