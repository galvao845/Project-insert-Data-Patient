<?php
namespace tests\Zeedhi\Framework\DBAL\Driver\OCI8;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Event\Listeners\OracleSessionInit;

class OCI8StatementTest extends \PHPUnit\Framework\TestCase {

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
        $this->connection->beginTransaction();
    }

    protected function tearDown() {
        $this->connection->rollBack();
    }

    public function testSelectLongRaw() {
        $rows = $this->connection->fetchAll('SELECT * FROM TEST_LONG_RAW ORDER BY ID');
        $this->assertCount(6, $rows);
        $lengthByRowIdx = array(3, 3, 3, 6, 5, 7);
        foreach ($lengthByRowIdx as $idx => $length) {
            $this->assertArrayHasKey($idx, $rows);
            $this->assertArrayHasKey("TEXT", $rows[$idx]);
            $this->assertEquals($length, strlen($rows[$idx]["TEXT"]));
        }
    }
}
