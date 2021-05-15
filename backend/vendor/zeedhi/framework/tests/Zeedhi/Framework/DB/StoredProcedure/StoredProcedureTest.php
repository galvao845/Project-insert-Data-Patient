<?php
namespace tests\Zeedhi\Framework\DB\StoredProcedure;

use Doctrine\DBAL\DriverManager;
use HumanRelation\Util\EntityManagerFactory;
use Zeedhi\Framework\DB\StoredProcedure\Param;
use Zeedhi\Framework\DB\StoredProcedure\StoredProcedure;
use Zeedhi\Framework\DB\StoredProcedure\Strategies\StrategyFactory;

class StoredProcedureTest extends \PHPUnit\Framework\TestCase {

    public function testCallProcedure() {
		$entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
		$storedProcedure = new StoredProcedure($entityManager->getConnection(), 'NEXT_CODE');
		$storedProcedure->addParam(new Param('PARAM_IN', Param::PARAM_INPUT, 1000, Param::PARAM_TYPE_INT, 4));
		$storedProcedure->addParam(new Param('PARAM_OUT', Param::PARAM_OUTPUT, null, Param::PARAM_TYPE_INT, 4));
		$outputValues = $storedProcedure->execute();
        $this->assertTrue(is_array($outputValues));
        $this->assertCount(1, $outputValues);
        $this->assertArrayHasKey("PARAM_OUT", $outputValues);
		$this->assertEquals((int)$outputValues['PARAM_OUT'], 1000);
	}

    public function testStoredProcedureWithParamInAndOut() {
        if(!extension_loaded('sqlsrv')) {
            $this->markTestSkipped("Can't execute this test, because the extension {sqlsrv} isn't present!");
        }
        $connection = EntityManagerFactory::createSqlServerEntityManager()->getConnection();
        $procedure = new StoredProcedure($connection, "MAX_SALARY");
        $procedure->addParam(new Param("JOB_ID", Param::PARAM_INPUT, "ST_CLERK"));
        $procedure->addParam(new Param("VALUE", Param::PARAM_OUTPUT, null, Param::PARAM_TYPE_INT));
        $returnValues = $procedure->execute();
        $this->assertTrue(is_array($returnValues));
        $this->assertCount(1, $returnValues);
        $this->assertArrayHasKey("VALUE", $returnValues[0]);
        $this->assertEquals(3600, $returnValues[0]["VALUE"]);
    }

    public function testStoredProcedureWithParamInOnlyOnSqlSrv() {
        if(!extension_loaded('sqlsrv')) {
            $this->markTestSkipped("Can't execute this test, because the extension {sqlsrv} isn't present!");
        }
        $connection = EntityManagerFactory::createSqlServerEntityManager()->getConnection();
        $procedure = new StoredProcedure($connection, "TESTE");
        $procedure->addParam(new Param("JOB_ID", Param::PARAM_INPUT, "ST_CLERK"));
        $returnValues = $procedure->execute();
        $this->assertTrue(is_array($returnValues));
        $this->assertCount(87, $returnValues);
        $this->assertArrayHasKey("first_name", $returnValues[0]);
        $this->assertEquals(24000, $returnValues[0]["salary"]);
    }

    public function testUnsupportedDriver() {
        $this->expectException('\Zeedhi\Framework\DB\StoredProcedure\Strategies\Exception');
        $this->expectExceptionMessage("Unsupported driver mysqli");
        StrategyFactory::createStrategy(DriverManager::getConnection(array(
            'driver'    => 'mysqli',
            'user'      => 'admin',
            'password'  => 'teknisa',
            'host'      => '192.168.122.65',
            'dbname'    => 'ZEEDHI_DEMO'
        )));
    }

    public function testPDODbLibStrategy() {
        $this->markTestSkipped("Database not ready to test!");
        if(!extension_loaded('pdo_dblib')) {
            // this extension is not available for windows.
            $this->markTestSkipped("Can't execute this test, because the extension isn't present!");
        }
        $connection = EntityManagerFactory::createDBLibSqlServerEntityManager()->getConnection();
        $procedure = new StoredProcedure($connection, "MAX_SALARY");
        $procedure->addParam(new Param("JOB_ID", Param::PARAM_INPUT, "ST_CLERK"));
        $procedure->addParam(new Param("VALUE", Param::PARAM_OUTPUT, null, Param::PARAM_TYPE_INT));
        $returnValues = $procedure->execute();
        $this->assertTrue(is_array($returnValues));
        $this->assertCount(1, $returnValues);
        $this->assertArrayHasKey("VALUE", $returnValues);
        $this->assertEquals(3600, $returnValues["VALUE"]);
    }

    public function testCallProcedureInOutParam() {
        $entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
        $storedProcedure = new StoredProcedure($entityManager->getConnection(), 'TESTEINPUTOUTPUT');
        $storedProcedure->addParam(new Param('PARAMETRO', Param::PARAM_INPUT_OUTPUT, 1000, Param::PARAM_TYPE_INT, 4));
        $outputValues = $storedProcedure->execute();

        $this->assertTrue(is_array($outputValues));
        $this->assertCount(1, $outputValues);
        $this->assertArrayHasKey("PARAMETRO", $outputValues);
        $this->assertEquals((int)$outputValues['PARAMETRO'], 500);
    }
}
