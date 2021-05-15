<?php
namespace tests\Zeedhi\Framework\DataSource\Manager;

use Zeedhi\Framework\DataSource\Configuration;

class ConfigurationTest extends \PHPUnit\Framework\TestCase {

    protected $fileLocation;

    protected function setUp() {
        $this->fileLocation = __DIR__ . "/../../../mocks/src/HumanRelation/Util/gen/datasources/";
    }

    public function testFactoryFromFileLocation() {
        $configuration = Configuration::factoryFromFileLocation($this->fileLocation, 'countries');
        $this->assertEquals("COUNTRIES", $configuration->getTableName());
        $this->assertContains("COUNTRY_ID", current($configuration->getPrimaryKeyColumns()));
        $this->assertCount(3, $configuration->getColumns());
        foreach(array("COUNTRY_ID", "COUNTRY_NAME", "REGION_ID") as $columnName) {
            $this->assertContains($columnName, $configuration->getColumns());
        }

        $this->assertNull($configuration->getSequentialColumn());

        $relation = current($configuration->getRelations());
        $this->assertEquals('REGIONS', $relation['targetTable']);
        $this->assertEquals('REGION_ID', $relation['targetSequentialColumn']);
        $this->assertEquals('REGION_ID', $relation['localSequentialColumn']);
        $this->assertEquals('REGION_ID', current($relation['localColumns']));
        $this->assertEquals('REGION_ID', current($relation['targetColumns']));

        $this->assertFalse($configuration->hasQuery());
    }

    public function testFactoryWithGroupBy() {
        $configuration = Configuration::factoryFromFileLocation($this->fileLocation, 'countries_group_by_regions');
        $this->assertEquals("COUNTRIES", $configuration->getTableName());
        $this->assertContains("COUNTRY_ID", current($configuration->getPrimaryKeyColumns()));
        $this->assertEquals(array("REGION_ID"), $configuration->getGroupBy());
    }

    public function testQueryConfiguration() {
        $configuration = Configuration::factoryFromFileLocation($this->fileLocation, 'countries_with_regions');
        $this->assertTrue($configuration->hasQuery());
        $query = "SELECT C.COUNTRY_ID, C.COUNTRY_NAME, C.REGION_ID, R.REGION_NAME FROM COUNTRIES C JOIN REGIONS R ON R.REGION_ID = C.REGION_ID";
        $this->assertEquals($query, $configuration->getQuery());
    }

    public function testInvalidFileName() {
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Exception");
        $this->expectExceptionMessage("Config file for data source invalid_file_name not found.");

        Configuration::factoryFromFileLocation($this->fileLocation, 'invalid_file_name');
    }

    public function testInvalidDataSourceNameWithValidFile() {
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Exception");
        $this->expectExceptionMessage("Data source invalid_data_source_name not found in config file.");

        Configuration::factoryFromFileLocation($this->fileLocation, 'invalid_data_source_name');
    }

    public function testPkColumnNotInDataSourceColumns() {
        $this->setExpectedException(
            "\\Zeedhi\\Framework\\DataSource\\Exception",
            "Primary key column PK_COLUMN not present in data source invalid_pk column list."
        );
        Configuration::factoryFromFileLocation($this->fileLocation, 'invalid_pk');
    }

    public function testSequentialColumnNoInPkColumns() {
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Exception");
        $this->expectExceptionMessage("Sequential column SEQ was not found in pk columns of data source invalid_seq.");

        Configuration::factoryFromFileLocation($this->fileLocation, 'invalid_seq');
    }

    public function testLoadDataColumnNamesDiffersFromColumnNames()  {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'simple_regions');
        $columns = $config->getColumns();
        $this->assertCount(2, $columns);
        $this->assertArrayHasKey('NAME', $columns);
        $this->assertEquals('REGION_NAME', $columns['NAME']);
        $this->assertArrayHasKey('ID', $columns);
        $this->assertEquals('REGION_ID', $columns['ID']);
    }

    public function testGetColumnByDataColumn () {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'simple_regions');
        $this->assertEquals('REGION_NAME', $config->getColumnByDataColumn('NAME'));
        $this->assertEquals('REGION_ID', $config->getColumnByDataColumn('ID'));
    }

    public function testDataColumnDoestNotExist() {
        $this->expectException("\\Zeedhi\\Framework\\DataSource\\Exception");
        $this->expectExceptionMessage("Data column INVALID_DATA_COLUMN does not exist in data source simple_regions");

        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'simple_regions');
        $config->getColumnByDataColumn('INVALID_DATA_COLUMN');
    }

    public function testGetDataColumns() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'simple_regions');
        $dataColumns = $config->getDataColumns();
        $this->assertContains('NAME', $dataColumns);
        $this->assertContains('ID', $dataColumns);
    }

    public function testGetColumnsInOldConfigFormat() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'regions');
        $columns = $config->getColumns();
        $this->assertCount(2, $columns);
        $this->assertArrayHasKey('REGION_NAME', $columns);
        $this->assertEquals('REGION_NAME', $columns['REGION_NAME']);
        $this->assertArrayHasKey('REGION_ID', $columns);
        $this->assertEquals('REGION_ID', $columns['REGION_ID']);
    }

    public function testGetDataColumnsInOldConfigFormat() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'regions');
        $dataColumns = $config->getDataColumns();
        $this->assertContains('REGION_NAME', $dataColumns);
        $this->assertContains('REGION_ID', $dataColumns);
    }

    public function testGetColumnByDataColumnInOldConfigFormat() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'regions');
        $this->assertEquals('REGION_NAME', $config->getColumnByDataColumn('REGION_NAME'));
        $this->assertEquals('REGION_ID', $config->getColumnByDataColumn('REGION_ID'));
    }

    public function testOrderBy() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'employees_ordered_by_name');
        $orderBy = $config->getOrderBy();
        $this->assertCount(1, $orderBy);
        $this->assertArrayHasKey('FIRST_NAME', $orderBy);
        $this->assertEquals('ASC', $orderBy['FIRST_NAME']);
    }

    public function testResultSetLimit() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'employees_ordered_by_name');
        $this->assertEquals(30, $config->getResultSetLimit());
    }

    public function testConditions() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'managers');
        $conditions = $config->getConditions();
        $this->assertCount(1, $conditions);
        $this->assertEquals("MANAGER_ID IS NULL", current($conditions));
    }

    public function testDataColumnThatDoesNotExist() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'simple_regions');
        $this->assertContains('NAME', $config->getDataColumnByColumn("REGION_NAME"));
        $msg = "Data column COUNTRY_NAME does not exist in data source simple_regions";
        $this->expectException('\Zeedhi\Framework\DataSource\Exception');
        $this->expectExceptionMessage($msg);

        $config->getDataColumnByColumn("COUNTRY_NAME");
    }
}