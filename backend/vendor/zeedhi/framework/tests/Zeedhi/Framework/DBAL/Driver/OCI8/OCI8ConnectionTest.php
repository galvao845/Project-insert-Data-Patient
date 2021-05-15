<?php
namespace tests\Zeedhi\Framework\DBAL\Driver\OCI8;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Event\Listeners\OracleSessionInit;

class OCI8ConnectionTest extends \PHPUnit\Framework\TestCase {

    /** @var Connection */
    private $connection;

    protected function setUp() {
        if (!extension_loaded('oci8')) {
            $this->markTestSkipped('oci8 is not installed.');
        }

        parent::setUp();

        $eventManager = new EventManager();
        $eventManager->addEventSubscriber(new OracleSessionInit());
        $this->connection = DriverManager::getConnection(array(
            'driverClass' => '\Zeedhi\Framework\DBAL\Driver\OCI8\Driver',
            'driver'      => 'oci8',
            'host'        => '192.168.122.5',
            'port'        => '1521',
            'user'        => 'USR_ORG_20',
            'password'    => 'teknisa',
            'dbname'      => 'pdborcl',
            'service'     => true
        ), null, $eventManager);
    }

    public function testPrepare() {
        $stmt = $this->connection->getWrappedConnection()->prepare("SELECT 1 FROM DUAL");
        $expectedClassName = 'Zeedhi\Framework\DBAL\Driver\OCI8\OCI8Statement';
        $this->assertInstanceOf($expectedClassName, $stmt, "Prepare must return a zeedhi oci statement.");
    }

    public function testFetchAllReturnNumericValues() {
        $stmt = $this->connection->prepare("SELECT 1+1 as \"INTEGER\", 7/2 as EXPRESSION, 1.618 as FLOAT_CONST FROM DUAL");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $this->assertCount(1, $rows);
        $this->assertTrue(is_array($row = $rows[0]));
        $this->assertArrayHasKey('INTEGER', $row);
        $this->assertEquals(2, $row['INTEGER']);
        $this->assertArrayHasKey('EXPRESSION', $row);
        $this->assertEquals(3.5, $row['EXPRESSION']);
        $this->assertArrayHasKey('FLOAT_CONST', $row);
        $this->assertEquals(1.618, $row['FLOAT_CONST']);
    }

    public function testFetchAssocReturnNumericValues() {
        $stmt = $this->connection->prepare("SELECT 1+1 as \"INTEGER\", 7/2 as EXPRESSION, 1.618 as FLOAT_CONST FROM DUAL");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertTrue(is_array($row));
        $this->assertArrayHasKey('INTEGER', $row);
        $this->assertEquals(2, $row['INTEGER']);
        $this->assertArrayHasKey('EXPRESSION', $row);
        $this->assertEquals(3.5, $row['EXPRESSION']);
        $this->assertArrayHasKey('FLOAT_CONST', $row);
        $this->assertEquals(1.618, $row['FLOAT_CONST']);
    }

    public function testFetchNumReturnNumericValues() {
        $stmt = $this->connection->prepare("SELECT 1+1 as \"INTEGER\", 7/2 as EXPRESSION, 1.618 as FLOAT_CONST FROM DUAL");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $this->assertTrue(is_array($row));
        $this->assertArrayHasKey(0, $row);
        $this->assertEquals(2, $row[0]);
        $this->assertArrayHasKey(1, $row);
        $this->assertEquals(3.5, $row[1]);
        $this->assertArrayHasKey(2, $row);
        $this->assertEquals(1.618, $row[2]);
    }

    public function testFetchColumnReturnNumericValues() {
        $stmt = $this->connection->prepare("SELECT 1+1 as \"INTEGER\" FROM DUAL");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_COLUMN);
        $this->assertTrue(is_array($row));
        $this->assertArrayHasKey(0, $row);
        $this->assertEquals(2, $row[0]);
    }

    public function testFetchAllWithAssocTypeReturnNumericValues() {
        $stmt = $this->connection->prepare("SELECT 1+1 as \"INTEGER\", 7/2 as EXPRESSION, 1.618 as FLOAT_CONST FROM DUAL");
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $rows);
        $this->assertTrue(is_array($row = $rows[0]));
        $this->assertArrayHasKey('INTEGER', $row);
        $this->assertEquals(2, $row['INTEGER']);
        $this->assertArrayHasKey('EXPRESSION', $row);
        $this->assertEquals(3.5, $row['EXPRESSION']);
        $this->assertArrayHasKey('FLOAT_CONST', $row);
        $this->assertEquals(1.618, $row['FLOAT_CONST']);
    }

    public function testOnlyDecimalParth() {
        $stmt = $this->connection->prepare('SELECT 1/2 as "MEIO" FROM DUAL');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_COLUMN);
        $this->assertTrue(is_array($row));
        $this->assertArrayHasKey(0, $row);
        $this->assertEquals(0.5, $row[0]);
    }

    public function testNoDecimalParth() {
        $stmt = $this->connection->prepare('SELECT 123456789 as "MILHOES" FROM DUAL');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_COLUMN);
        $this->assertTrue(is_array($row));
        $this->assertArrayHasKey(0, $row);
        $this->assertEquals(123456789, $row[0]);
    }

    public function testFetchNumberNullValue() {
        $stmt = $this->connection->prepare("SELECT * FROM EMPLOYEES WHERE first_name LIKE 'Steven' ORDER BY EMPLOYEE_ID ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $this->assertCount(2, $rows);

        $this->assertEquals(100, $rows[0]['EMPLOYEE_ID']);
        $this->assertNull($rows[0]['MANAGER_ID']);
        $this->assertEquals(128, $rows[1]['EMPLOYEE_ID']);
        $this->assertEquals(120, $rows[1]['MANAGER_ID']);
    }

}
