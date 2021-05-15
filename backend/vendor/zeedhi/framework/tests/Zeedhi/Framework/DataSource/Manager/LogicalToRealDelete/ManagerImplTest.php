<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\LogicalToRealDelete;

use Doctrine\ORM\EntityManager;
use HumanRelation\Entities\Regions;
use HumanRelation\Util\DataSource\NameProvider;
use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\Manager\Doctrine\ManagerImpl;
use Zeedhi\Framework\DataSource\Manager\LogicalToRealDelete\ManagerImpl as LogicalManager;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Row;

class ManagerImplTest extends \PHPUnit\Framework\TestCase {

	/** @var LogicalManager */
	protected $logicalManagerImpl;
	/** @var EntityManager */
	protected $entityManager;

	public function setUp() {
		$entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
		$parameterBag = new ParameterBag(new ArrayImpl());
		$managerImpl = new ManagerImpl($entityManager, new NameProvider(), $parameterBag);
		$this->logicalManagerImpl = new LogicalManager($managerImpl, '__isDeleted', true, false);
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

	public function testFindByAll() {
		static $regionsColumns = array("REGION_ID", "REGION_NAME", "__is_new", "__isDeleted");
		/** @var DataSet $dataSet */
		$dataSet = $this->logicalManagerImpl->findBy(new FilterCriteria("regions"));
		$this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
		$this->assertCount(4, $dataSet->getRows(), "Find all regions should retrieve 4 rows");
		foreach ($dataSet->getRows() as $row) {
			$this->assertTrue(is_array($row));
			foreach ($row as $column => $value) {
				$this->assertContains($column, $regionsColumns, "Column must be one of know columns");
				$this->assertNotNull($value, "Value of columns must be not null");
			}

			$this->assertFalse($row["__is_new"], "All rows must not be new.");
			$this->assertFalse($row["__isDeleted"], "All rows must not be deleted.");
		}
	}

	public function testPersistMixed() {
		$countries = array(
			array(
				"__is_new" => false,
				"__isDeleted" => true,
				"COUNTRY_ID" => "AR",
				"COUNTRY_NAME" => "Argentina",
				"REGION_ID" => 2
			),
			array(
				"__is_new" => true,
				"__isDeleted" => false,
				"COUNTRY_ID" => "BG",
				"COUNTRY_NAME" => "Bulgaria",
				"REGION_ID" => 1
			)
		);

		$dataSet = new DataSet("countries", $countries);
		$persistedRows = $this->logicalManagerImpl->persist($dataSet);
		$this->assertCount(2, $persistedRows, "Number of persisted rows must be 2.");
		$country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "BG");
		$this->assertInstanceOf("\\HumanRelation\\Entities\\Countries", $country, "A country with id BG must exist.");
		$this->assertEquals("Bulgaria", $country->getCountryName(), "Name of country with id BG must be 'Bulgaria'.");

		$country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
		$this->assertNull($country, "Country 'Argentina' must not exists anymore.");
	}

	public function testDelete() {
		$row = array(
			"__is_new" => false,
			"COUNTRY_ID" => "AR",
			"COUNTRY_NAME" => "Argentina",
			"REGION_ID" => 2
		);
		$dataSet = new DataSet("countries", array($row));
		$deletedRows = $this->logicalManagerImpl->delete($dataSet);
		$this->assertCount(1, $deletedRows, "Number of deleted rows must be 1.");
		/** @var Regions $region */
		$region = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
		$this->assertNull($region, "Region 'Argentina' must not exists anymore.");
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

		$dataColumnConversionManager = new LogicalManager($managerMock, '__isDeleted', true, false);

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
								array('REGION_ID' => 1, 'REGION_NAME' => 'Europe', '__is_new' => false, '__isDeleted' => false),
								array('REGION_ID' => 2, 'REGION_NAME' => 'Americas', '__is_new' => false, '__isDeleted' => false),
								array('REGION_ID' => 3, 'REGION_NAME' => 'Asia', '__is_new' => false, '__isDeleted' => false),
								array('REGION_ID' => 4, 'REGION_NAME' => 'Middle East and Africa', '__is_new' => false, '__isDeleted' => false)
						)
				))
		));

		$finalResult = $this->logicalManagerImpl->populateDataSet($dataSet);

		$this->assertEquals($expectedResult, $finalResult);
	}

	public function testBuildAllDataFilter(){
		$value = array('__ALL' => array(array('name' => 'REGION_NAME', 'operator' => '=', 'value' => 'Europe')));
		$rows = array(array('column_EXCEPT' => array(4), 'column' => $value));
		$expectedFilterCriteria = new FilterCriteria('simple_regions');
		$expectedFilterCriteria->addCondition('column', "NOT_IN", array(4));
		$expectedFilterCriteria->addCondition('REGION_NAME', '=', 'Europe');
		$finalResult = $this->logicalManagerImpl->buildAllDataFilter($rows, $value, 'column', 'simple_regions');

		$this->assertEquals($expectedFilterCriteria, $finalResult);
	}
}
