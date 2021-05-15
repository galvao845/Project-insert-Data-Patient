<?php
namespace tests\Zeedhi\Framework\DataSource\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use HumanRelation\Entities\Countries;
use HumanRelation\Entities\Regions;
use HumanRelation\Util\EntityManagerFactory;
use Zeedhi\Framework\Cache\Type\ArrayImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\ParameterBag;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\ORM\DateTime;

abstract class ManagerTestCase extends \PHPUnit\Framework\TestCase {

    /** @var Manager */
    protected $dataSourceManager;
    /** @var EntityManager */
    protected $entityManager;
    /** @var ParameterBag */
    protected $parameterBag;
    /** @var Connection */
    protected $connection;

    /**
     * @return Manager
     */
    abstract protected function getDataSourceManager();

    /**
     * @return EntityManager
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getEntityManager() {
        return EntityManagerFactory::createWithOracleConnection();
    }

    public function setUp() {
        parent::setUp();
        $this->entityManager = $this->getEntityManager();
        $this->connection = $this->entityManager->getConnection();
        $this->parameterBag = $parameterBag = new ParameterBag(new ArrayImpl());
        $this->dataSourceManager = $this->getDataSourceManager();
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
        if($this->entityManager) $this->entityManager->rollback();
    }

    public function testFindByAll() {
        static $regionsColumns = array("REGION_ID", "REGION_NAME", "__is_new");
        /** @var DataSet $dataSet */
        $dataSet = $this->dataSourceManager->findBy(new FilterCriteria("regions"));
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(4, $dataSet->getRows(), "Find all regions should retrieve 4 rows");
        foreach($dataSet->getRows() as $row) {
            $this->assertTrue(is_array($row));
            foreach($row as $column => $value) {
                $this->assertContains($column, $regionsColumns, "Column must be one of know columns");
                $this->assertNotNull($value, "Value of columns must be not null");
            }

            $this->assertArrayHasKey("__is_new", $row, "All rows must have column '__is_new'.");
            $this->assertFalse($row["__is_new"], "All rows must not be new.");
        }
    }

    public function testFindByWithFilter() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition("REGION_NAME", "Americas");
        /** @var DataSet $dataSet */
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");
        foreach($dataSet->getRows() as $row) {
            $this->assertEquals("Americas", $row["REGION_NAME"], "All retrieved regions should name 'Americas'.");
        }
    }

    public function testFindByWithRelationInFilter() {
        /** @var DataSet $dataSet */
        $filterCriteria = new FilterCriteria("countries");
        $filterCriteria->addCondition("REGION_ID", 1);
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet");
        foreach ($dataSet->getRows() as $row) {
            $this->assertEquals(1, $row["REGION_ID"], "All retrieved countries should be in 'Americas'.");
        }
    }

    public function testFindByWithPagination() {
        $filterCriteria = new FilterCriteria("countries");
        $filterCriteria->setPage(1);
        $filterCriteria->setPageSize(20);
        /** @var DataSet $dataSet */
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(20, $dataSet->getRows(), "Should return 20 rows.");
    }

    public function testPersistInsert() {
        $row = array(
            "__is_new"    => true,
            "REGION_ID"   => 6,
            "REGION_NAME" => "Antarctica"
        );
        $dataSet = new DataSet("regions", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 6 must exist.");
        $this->assertEquals("Antarctica", $region->getRegionName(), "Name of region with id 6 must be 'Antarctica'.");
    }


    public function testPersistUpdate() {
        $row = array(
            "__is_new"    => false,
            "REGION_ID"   => 4,
            "REGION_NAME" => "Africa"
        );
        $dataSet = new DataSet("regions", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 4);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 4 must exist.");
        $this->assertEquals("Africa", $region->getRegionName(), "Name of region with id 4 must be 'Africa'.");
    }

    public function testPersistMixed() {
        $newRegion = array(
            "__is_new"    => true,
            "REGION_ID"   => 6,
            "REGION_NAME" => "Antarctica"
        );

        $oldRegion = array(
            "__is_new"    => false,
            "REGION_ID"   => 4,
            "REGION_NAME" => "Africa" // Original name is "Middle East and Africa"
        );

        $regions = array($oldRegion, $newRegion);
        $dataSet = new DataSet("regions", $regions);
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(2, $persistedRows, "Number of persisted rows must be 2.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 6 must exist.");
        $this->assertEquals("Antarctica", $region->getRegionName(), "Name of region with id 6 must be 'Antarctica'.");

        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 4);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 4 must exist.");
        $this->assertEquals("Africa", $region->getRegionName(), "Name of region with id 4 must be 'Africa'.");
    }

    public function testDelete() {
        $row = array(
            "__is_new"     => false,
            "COUNTRY_ID"   => "AR",
            "COUNTRY_NAME" => "Argentina",
            "REGION_ID"    => 2
        );
        $dataSet = new DataSet("countries", array($row));
        $deletedRows = $this->dataSourceManager->delete($dataSet);
        $this->assertCount(1, $deletedRows, "Number of deleted rows must be 1.");
        /** @var Countries $country */
        $country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
        $this->assertNull($country, "Country 'Argentina' must not exists anymore.");
    }

    public function testFindByWithWhereClause() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->setWhereClause("REGION_NAME LIKE 'Americas'");
        /** @var DataSet $dataSet */
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");
        foreach($dataSet->getRows() as $row) {
            $this->assertEquals("Americas", $row["REGION_NAME"], "All retrieved regions should name 'Americas'.");
        }
    }

    public function testInsertCompositePk() {
        $row = array(
            "__is_new"      => true,
            "EMPLOYEE_ID"   => 100,
            "START_DATE"    => "2014-03-19 00:00:00",
            "END_DATE"      => "2014-08-31 00:00:00",
            "JOB_ID"        => "AD_ASST",
            "DEPARTMENT_ID" => 60
        );

        $dataSet = new DataSet("job_history", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $jobHistory */
        $jobHistoryId = array(
            "employee"  => $this->entityManager->find("\\HumanRelation\\Entities\\Employees", 100),
            "startDate" => DateTime::createFromFormat("d/m/Y H:i:s", "19/03/2014 00:00:00")
        );

        $jobHistory = $this->entityManager->find("\\HumanRelation\\Entities\\JobHistory", $jobHistoryId);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\JobHistory", $jobHistory, "The new history must exist.");
    }

    public function testFindByWithWhereClauseAndParameters() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->setWhereClause("REGION_NAME LIKE :REGION_NAME", array("REGION_NAME" => "Americas"));
        /** @var DataSet $dataSet */
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");
        foreach($dataSet->getRows() as $row) {
            $this->assertEquals("Americas", $row["REGION_NAME"], "All retrieved regions should name 'Americas'.");
        }
    }

    public function testFindByWithLimitedColumns() {
        $columnList = array(
            "LOCATION_ID",
            "CITY",
            "STATE_PROVINCE",
            "COUNTRY_ID",
            "__is_new" // will be added by data source manager for additional control info.
        );
        $filterCriteria = new FilterCriteria("locations");
        $filterCriteria->addCondition("CITY", "Sydney");
        /** @var DataSet $dataSet */
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertInstanceOf("\\Zeedhi\\Framework\\DataSource\\DataSet", $dataSet, "Return must be a instance of DataSet.");
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 row.");
        foreach($dataSet->getRows() as $row) {
            foreach($row as $column => $value) {
                $this->assertContains($column, $columnList, "Column must be in data source column list");
            }
        }
    }

    public function testFindByRelationsWithNullValues() {
        $filterCriteria = new FilterCriteria("employees");
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(107, $dataSet->getRows());
    }

    public function testFindByWithOperatorIn() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition("REGION_ID", FilterCriteria::IN, array(1, 2));
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(2, $dataSet->getRows(), "Should return 2 rows.");
    }

    public function testFindByWithOperatorNotIn() {
        $filterCriteria = new FilterCriteria("regions");
        $filterCriteria->addCondition("REGION_ID", FilterCriteria::NOT_IN, array(1, 2));
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(2, $dataSet->getRows(), "Should return 2 rows");
    }

    public function testFindByInQueryDataSource() {
        $expectedRow = array(
            'COUNTRY_ID'   => 'BR',
            'COUNTRY_NAME' => 'Brazil',
            'REGION_ID'    => 2,
            'REGION_NAME'  => 'Americas',
        );
        $filterCriteria = new FilterCriteria("countries_with_regions");
        $filterCriteria->addCondition("COUNTRY_ID", FilterCriteria::EQ, 'BR');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 rows");
        $row = @array_pop($dataSet->getRows());
        foreach($expectedRow as $columnName => $columnValue) {
            $this->assertArrayHasKey($columnName, $row);
            $this->assertEquals($columnValue, $row[$columnName]);
        }
    }

    public function testFindAssertingColumnTypesInRelation() {
        $filterCriteria = new FilterCriteria("countries");
        $filterCriteria->addCondition("COUNTRY_ID", FilterCriteria::EQ, 'BR');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(1, $dataSet->getRows(), "Should return 1 rows");
        $row = @array_pop($dataSet->getRows());
        $this->assertTrue(is_string($row['COUNTRY_ID']));
        $this->assertTrue(is_string($row['COUNTRY_NAME']));
        $this->assertTrue($row['REGION_ID'] === (int)$row['REGION_ID']);
    }

    public function testFindWithExtraColumnInDataSourceConfig() {
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Manager\\Exception");
        $this->expectExceptionMessage("Column EXTRA_COLUMN was not found in result set of data source invalid_column_countries.");

        $filterCriteria = new FilterCriteria("invalid_column_countries");
        $filterCriteria->addCondition("COUNTRY_ID", FilterCriteria::EQ, 'BR');
        $this->dataSourceManager->findBy($filterCriteria);
    }

    public function testFullSearch() {
        $expectedCountryIds = array('AU', 'US');
        $filterCriteria = new FilterCriteria("countries");
        $filterCriteria->addCondition("*", FilterCriteria::LIKE_ALL, '%US%');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(2, $rows);
        foreach($rows as $row){
            $this->assertContains($row['COUNTRY_ID'], $expectedCountryIds);
        }
    }

    public function testFullSearchWithColumnList() {
        $expectedCountryIds = array('AU', 'US');
        $filterCriteria = new FilterCriteria("countries");
        $filterCriteria->addCondition("COUNTRY_ID|COUNTRY_NAME", FilterCriteria::LIKE_ALL, '%US%');
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(2, $rows);
        foreach($rows as $row){
            $this->assertContains($row['COUNTRY_ID'], $expectedCountryIds);
        }
    }

    public function testUpdateAssociationToNull() {
        $row = array(
            "__is_new"     => false,
            "COUNTRY_ID"   => "AR",
            "COUNTRY_NAME" => "Argentina",
            "REGION_ID"    => null
        );
        $dataSet = new DataSet("countries", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Countries $country */
        $country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Countries", $country);
        $this->assertNull($country->getRegion(), "Region must be null.");
    }

    public function testQueryWithoutTable() {
        $dataSet = $this->dataSourceManager->findBy(new FilterCriteria("sysdate"));
        $rows = $dataSet->getRows();
        $this->assertCount(1, $rows);
        $this->assertArrayHasKey("SYSDATE", $rows[0]);
    }

    public function testOrderByAndResultSetLimit() {
        $dataSet = $this->dataSourceManager->findBy(new FilterCriteria("employees_ordered_by_name"));
        $rows = $dataSet->getRows();
        $this->assertCount(30, $rows);
        $previousRow = $rows[0];
        for ($i = 1; $i < 30; $i++) {
            $currentRow = $rows[$i];
            $this->assertLessThanOrEqual(0, strcmp($previousRow['FIRST_NAME'], $currentRow['FIRST_NAME']));
        }
    }

    /**
     * Test WhereClause, ProjectCondition and Condition.
     */
    public function testConditions() {
        foreach ($this->dataSourceManager->findBy(new FilterCriteria("managers"))->getRows() as $row) {
            $this->assertNull($row['MANAGER_ID']);
        }
    }

    public function testConditionWithParam() {
        $jobId = 'IT_PROG';
        $this->parameterBag->set('JOB_ID', $jobId);
        $dataSet = $this->dataSourceManager->findBy(new FilterCriteria("employees_by_job"));
        foreach ($dataSet->getRows() as $row) {
            $this->assertEquals($jobId, $row['JOB_ID']);
        }
    }

    public function testWhereClauseWithTwoDotsInParameters() {
        $jobId = 'IT_PROG';
        $filterCriteria = new FilterCriteria("employees");
        $filterCriteria->setWhereClause("JOB_ID = :JOB_ID", array(':JOB_ID' => $jobId));
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        foreach ($dataSet->getRows() as $row) {
            $this->assertEquals($jobId, $row['JOB_ID']);
        }
    }

    public function testOrderByFromFilterCriteria() {
        $filterCriteria = new FilterCriteria("employees");
        $filterCriteria->addOrderBy('FIRST_NAME', FilterCriteria::ORDER_ASC);
        $filterCriteria->setPage(1);
        $filterCriteria->setPageSize(30);
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $rows = $dataSet->getRows();
        $this->assertCount(30, $rows);
        $previousRow = $rows[0];
        for ($i = 1; $i < 30; $i++) {
            $currentRow = $rows[$i];
            $this->assertLessThanOrEqual(
                0,
                strcmp($previousRow['FIRST_NAME'], $currentRow['FIRST_NAME']),
                "Previous row name must be 'lower or equal' than the next name"
            );
        }
    }

    public function testPersistTransactionControl() {
        $this->connection->rollBack(); //pairing with setUp beginTransaction
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Manager\\Exception");
        $rows = array(
            array(
                "__is_new"    => true,
                "REGION_ID"   => 6,
                "REGION_NAME" => "Antarctica"
            ),
            array(
                "__is_new"    => true,
                "REGION_ID"   => 7,
                "REGION_NAME" => "abcdefghijklmnopqrstuvwxyz"
            )
        );

        $dataSet = new DataSet("regions", $rows);
        try {
            $this->dataSourceManager->persist($dataSet);
            $this->assertFalse(true, "This must be skipped by exception.");
        } catch (\Exception $e) {
            /** @var Regions $region */
            $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
            $this->assertNull($region, "Region 6 must not be finded.");
            $this->connection->beginTransaction(); //pairing with tearDown beginTransaction
            throw $e;
        }
    }

    public function testDeleteTransactionControl() {
        $this->connection->rollBack(); //pairing with setUp beginTransaction
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Manager\\Exception");
        $rows = array(
            array(
                "__is_new"     => false,
                "COUNTRY_ID"   => "AR",
                "COUNTRY_NAME" => "Argentina",
                "REGION_ID"    => 2
            ),
            array(
                "__is_new"     => false,
                "COUNTRY_ID"   => "DK",
                "COUNTRY_NAME" => "Denmark",
                "REGION_ID"    => 1
            )
        );
        $dataSet = new DataSet("regions", $rows);
        try {
            $this->dataSourceManager->delete($dataSet);
            $this->assertFalse(true, "This must be skipped by exception.");
        } catch (\Exception $e) {
            $country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
            $this->assertInstanceOf("\\HumanRelation\\Entities\\Countries", $country, "Country Argentina must be found.");
            $this->connection->beginTransaction(); //pairing with tearDown beginTransaction
            throw $e;
        }
    }

    public function testFindByWithOperatorInOnDataSourceQuery() {
        $filterCriteria = new FilterCriteria("countries_with_regions");
        $filterCriteria->addCondition("COUNTRY_ID", FilterCriteria::IN, array('BR', 'US'));
        $dataSet = $this->dataSourceManager->findBy($filterCriteria);
        $this->assertCount(2, $dataSet->getRows(), "Should return 2 rows");
    }

    public function testPersistInsertWithRowObject() {
        $row = new Row(array(
            "__is_new"    => true,
            "REGION_ID"   => 6,
            "REGION_NAME" => "Antarctica"
        ));
        $dataSet = new DataSet("regions", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 6);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 6 must exist.");
        $this->assertEquals("Antarctica", $region->getRegionName(), "Name of region with id 6 must be 'Antarctica'.");
    }

    public function testPersistUpdateWithRowObject() {
        $row = new Row(array(
            "__is_new"    => false,
            "REGION_ID"   => 4,
            "REGION_NAME" => "Africa"
        ));
        $dataSet = new DataSet("regions", array($row));
        $persistedRows = $this->dataSourceManager->persist($dataSet);
        $this->assertCount(1, $persistedRows, "Number of persisted rows must be 1.");
        /** @var Regions $region */
        $region = $this->entityManager->find("\\HumanRelation\\Entities\\Regions", 4);
        $this->assertInstanceOf("\\HumanRelation\\Entities\\Regions", $region, "A region with id 4 must exist.");
        $this->assertEquals("Africa", $region->getRegionName(), "Name of region with id 4 must be 'Africa'.");
    }

    public function testDeleteWithRowObject() {
        $row = new Row(array(
            "__is_new"     => false,
            "COUNTRY_ID"   => "AR",
            "COUNTRY_NAME" => "Argentina",
            "REGION_ID"    => 2
        ));
        $dataSet = new DataSet("countries", array($row));
        $deletedRows = $this->dataSourceManager->delete($dataSet);
        $this->assertCount(1, $deletedRows, "Number of deleted rows must be 1.");
        /** @var Countries $country */
        $country = $this->entityManager->find("\\HumanRelation\\Entities\\Countries", "AR");
        $this->assertNull($country, "Country 'Argentina' must not exists anymore.");
    }
}