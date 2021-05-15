<?php
namespace tests\Zeedhi\Framework\DataSource\SyncEngine;

use HumanRelation\Entities\Regions;
use HumanRelation\Util\DataSource\NameProvider;
use Doctrine\ORM\EntityManager;
use tests\Zeedhi\Framework\ApplicationMocks\KernelImpl;
use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager\Doctrine\ManagerImpl;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DataSource\SyncEngine\Manager;
use Zeedhi\Framework\DataSource\SyncEngine\MaxPlus1SyncEngineImpl;
use Zeedhi\Framework\DTO\Row;

class ManagerTest extends \PHPUnit\Framework\TestCase {

	/** @var EntityManager */
	protected $entityManager;
	/** @var Manager */
	protected $syncEngineManager;

	public function setUp() {
		$this->entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
		$nameProvider = new NameProvider();
		$parameterBag = new ParameterBag(new ArrayImpl());
		$this->syncEngineManager = new Manager(
			new ManagerImpl($this->entityManager, $nameProvider, $parameterBag),
			new MaxPlus1SyncEngineImpl(new ArrayImpl(), $this->entityManager->getConnection()),
			new KernelImpl(),
			$nameProvider
		);

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

	public function testPersist() {
		$row = array(
			"__is_new" => true,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica"
		);
		$dataSet = new DataSet("regions", array($row));
		$persistedRows = $this->syncEngineManager->persist($dataSet);
		$this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
		$persistedRow = current($persistedRows);
		$this->assertEquals("unsync_1000", $persistedRow['REGION_ID'], "Persisted row id must be unsynchronized.");
		/** @var Regions $region */
		$region = $this->entityManager->getRepository("\\HumanRelation\\Entities\\Regions")->findOneBy(array("regionName" => "Antarctica"));
		$this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with that name must exist.");
		$this->assertEquals(5, $region->getRegionId(), "Id of region with name 'Antarctica' must be 5.");
		// end of insert test ||| init of update test
		$row = array(
			"__is_new" => false,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica Atualizada"
		);
		$dataSet = new DataSet("regions", array($row));
		$persistedRows = $this->syncEngineManager->persist($dataSet);
		$this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
		$persistedRow = current($persistedRows);
		$this->assertEquals("unsync_1000", $persistedRow['REGION_ID'], "Updated row id must be unsynchronized.");
		/** @var Regions $updatedRegion */
		$updatedRegion = $this->entityManager->getRepository("\\HumanRelation\\Entities\\Regions")->findOneBy(array("regionName" => "Antarctica Atualizada"));
		$this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $updatedRegion, "A region with that name must exist.");
		$this->assertEquals($region->getRegionId(), $updatedRegion->getRegionId(), "Must be the same region as before");

		$row = array(
			"__is_new" => false,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica Atualizada"
		);
		$dataSet = new DataSet("regions", array($row));
		$deletedRows = $this->syncEngineManager->delete($dataSet);
		$this->assertCount(1, $deletedRows, "Number of deleted rows must be 1.");
		$deletedRow = current($deletedRows);
		$this->assertEquals("unsync_1000", $deletedRow['REGION_ID'], "Deleted row id must be unsynchronized.");
		$region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", $updatedRegion->getRegionId());
		$this->assertNull($region, "Region 'Antarctica' must not exists anymore.");
	}

	public function testFindByAll() {
		// Find all must work as before.
		static $regionsColumns = array("REGION_ID", "REGION_NAME", "__is_new");
		/** @var DataSet $dataSet */
		$dataSet = $this->syncEngineManager->findBy(new FilterCriteria("regions"));
		$this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
		$this->assertCount(4, $dataSet->getRows(), "Find all regions should retrieve 4 rows");
		foreach ($dataSet->getRows() as $row) {
			$this->assertTrue(is_array($row));
			foreach ($row as $column => $value) {
				$this->assertContains($column, $regionsColumns, "Column must be one of know columns");
				$this->assertNotNull($value, "Value of columns must be not null");
			}

			$this->assertFalse($row["__is_new"], "All rows must not be new.");
		}
	}

	public function testRelationSync() {
		$row = array(
			"__is_new" => true,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica"
		);
		$dataSet = new DataSet("regions", array($row));
		$persistedRows = $this->syncEngineManager->persist($dataSet);
		$this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");

		$row = array(
			"__is_new" => true,
			"COUNTRY_ID" => "NC",
			"COUNTRY_NAME" => "New Country",
			"REGION_ID" => "unsync_1000"
		);
		$dataSet = new DataSet("countries", array($row));
		$persistedRows = $this->syncEngineManager->persist($dataSet);
		$this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
		/** @var \HumanRelation\Entities\Countries $country */
		$country = $this->entityManager->getRepository("\\HumanRelation\\Entities\\Countries")->findOneBy(array("countryName" => "New Country"));
		$this->assertInstanceOf("\\HumanRelation\\Entities\\Countries", $country);
		$this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $country->getRegion());
		$this->assertEquals("Antarctica", $country->getRegion()->getRegionName());

		$filterCriteria = new FilterCriteria("countries");
		$filterCriteria->addCondition('COUNTRY_ID', "NC");
		$dataSet = $this->syncEngineManager->findBy($filterCriteria);
		$retrievedRow = current($dataSet->getRows());
		$this->assertEquals("unsync_1000", $retrievedRow['REGION_ID']);
	}

	public function testFindAllAfterPersistedRows() {
		$row = array(
			"__is_new" => true,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica"
		);
		$this->syncEngineManager->persist(new DataSet("regions", array($row)));

		/** @var DataSet $dataSet */
		$filterCriteria = new FilterCriteria("regions");
		$filterCriteria->addCondition("REGION_ID", "unsync_1000");
		$dataSet = $this->syncEngineManager->findBy($filterCriteria);
		$this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
		$rows = $dataSet->getRows();
		$this->assertCount(1, $rows, "Find all organizations should retrieve 1 row");
		$row = current($rows);
		$this->assertEquals('Antarctica', $row['REGION_NAME']);
		$this->assertEquals("unsync_1000", $row['REGION_ID']);
	}

	public function testFindAllRelationSync() {
		//@todo make a test that really broken if wrong.
		$row = array(
			"__is_new" => true,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica"
		);
		$dataSet = new DataSet("regions", array($row));
		$this->syncEngineManager->persist($dataSet);
		$row = array(
			"__is_new" => true,
			"COUNTRY_ID" => "NC",
			"COUNTRY_NAME" => "New Country",
			"REGION_ID" => "unsync_1000"
		);
		$dataSet = new DataSet("countries", array($row));
		$this->syncEngineManager->persist($dataSet);
		$filterCriteria = new FilterCriteria("countries");
		$filterCriteria->addCondition("REGION_ID", "unsync_1000");
		$dataSet = $this->syncEngineManager->findBy($filterCriteria);
		$retrievedRow = current($dataSet->getRows());
		$this->assertEquals("New Country", $retrievedRow['COUNTRY_NAME']);
		$this->assertEquals("NC", $retrievedRow['COUNTRY_ID']);
	}

	public function testUseWithRowObject() {
		$row = new Row(array(
			"__is_new" => true,
			"REGION_ID" => "unsync_1000",
			"REGION_NAME" => "Antarctica"
		));
		$dataSet = new DataSet("regions", array($row));
		$persistedRows = $this->syncEngineManager->persist($dataSet);
		$this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
		$persistedRow = current($persistedRows);
		$this->assertEquals("unsync_1000", $persistedRow['REGION_ID'], "Persisted row id must be unsynchronized.");
		/** @var Regions $region */
		$region = $this->entityManager->getRepository("\\HumanRelation\\Entities\\Regions")->findOneBy(array("regionName" => "Antarctica"));
		$this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with that name must exist.");
		$this->assertEquals(5, $region->getRegionId(), "Id of region with name 'Antarctica' must be 5.");
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

		$dataColumnConversionManager = new Manager(
			$managerMock,
			new MaxPlus1SyncEngineImpl(new ArrayImpl(), $this->entityManager->getConnection()),
			new KernelImpl(),
			new NameProvider()
		);

		$filterCriteria = new FilterCriteria("full_countries_with_regions");
		$filterCriteria->addOrderBy("COUNTRY_NAME", "ASC");
		$dataSet = $dataColumnConversionManager->findBy($filterCriteria);
		$this->assertEquals("full_countries_with_regions", $dataSet->getDataSourceName());
		$this->assertCount(0, $dataSet->getRows());
	}

    public function testFindAllRelationSyncWithInOperator() {
        $row = array(
            "__is_new" => true,
            "REGION_ID" => "unsync_1000",
            "REGION_NAME" => "Antarctica"
        );
        $dataSet = new DataSet("regions", array($row));
        $this->syncEngineManager->persist($dataSet);
        $row = array(
            "__is_new" => true,
            "COUNTRY_ID" => "NC",
            "COUNTRY_NAME" => "New Country",
            "REGION_ID" => "unsync_1000"
        );
        $dataSet = new DataSet("countries", array($row));
        $this->syncEngineManager->persist($dataSet);
        $filterCriteria = new FilterCriteria("countries");
        $filterCriteria->addCondition("REGION_ID", FilterCriteria::IN, array("unsync_1000"));
        $dataSet = $this->syncEngineManager->findBy($filterCriteria);
        $retrievedRow = current($dataSet->getRows());
        $this->assertEquals("New Country", $retrievedRow['COUNTRY_NAME']);
        $this->assertEquals("NC", $retrievedRow['COUNTRY_ID']);
    }

    public function testFindAllInPkWithInOperator() {
        $row = array(
            "__is_new" => true,
            "REGION_ID" => "unsync_1000",
            "REGION_NAME" => "Antarctica"
        );
        $this->syncEngineManager->persist(new DataSet("regions", array($row)));

        /** @var DataSet $dataSet */
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition("REGION_ID", FilterCriteria::IN, array("unsync_1000"));
        $dataSet = $this->syncEngineManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $rows = $dataSet->getRows();
        $this->assertCount(1, $rows, "Find all organizations should retrieve 1 row");
        $row = current($rows);
        $this->assertEquals('Antarctica', $row['REGION_NAME']);
        $this->assertEquals("unsync_1000", $row['REGION_ID']);
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

			$finalResult = $this->syncEngineManager->populateDataSet($dataSet);

			$this->assertEquals($expectedResult, $finalResult);
		}

    public function testBuildAllDataFilter(){
        $value = array('__ALL' => array(array('name' => 'NAME', 'operator' => '=', 'value' => 'Europe')));
        $rows = array(array('column_EXCEPT' => array(4), 'column' => $value));
        $expectedFilterCriteria = new FilterCriteria('simple_regions');
        $expectedFilterCriteria->addCondition('column', "NOT_IN", array(4));
        $expectedFilterCriteria->addCondition('NAME', '=', 'Europe');
        $finalResult = $this->syncEngineManager->buildAllDataFilter($rows, $value, 'column', 'simple_regions');

        $this->assertEquals($expectedFilterCriteria, $finalResult);
    }
}