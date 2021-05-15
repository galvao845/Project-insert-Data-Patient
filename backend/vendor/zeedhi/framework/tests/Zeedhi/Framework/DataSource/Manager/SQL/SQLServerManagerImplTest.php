<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\SQL;

use HumanRelation\Util\EntityManagerFactory;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager;

use Doctrine\DBAL\DBALException;

class SQLServerManagerImplTest extends ManagerImplTest {

    public function setUp() {
        $this->markTestSkipped('Database is not ready to run the tests.');

        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped('The SQLServer (sqlsrv) extension is not available.');
        }

        parent::setUp();
    }


    protected function getEntityManager() {
        return EntityManagerFactory::createSqlServerEntityManager();
    }

    public function testQueryWithoutTable() {
        $this->markTestSkipped("Can't use SYSDATE AND DUAL!");
    }

    public function testFindWithExtraColumnInDataSourceConfig() {
        $this->expectException(DBALException::class);
        $filterCriteria = new FilterCriteria("invalid_column_countries");
        $filterCriteria->addCondition("COUNTRY_ID", FilterCriteria::EQ, 'BR');
        $this->dataSourceManager->findBy($filterCriteria);
    }


}