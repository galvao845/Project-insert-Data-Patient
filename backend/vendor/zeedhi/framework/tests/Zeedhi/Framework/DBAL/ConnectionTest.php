<?php
namespace tests\Zeedhi\Framework\DBAL;

use Doctrine\DBAL\Driver\DriverException;
use Zeedhi\Framework\DBAL;

class ConnectionTest extends \PHPUnit_Framework_TestCase {

    /** @var DBAL\Connection */
    protected $connection;

    protected function setUp() {
        $this->connection = new DBAL\Connection([], new DBAL\Driver\OCI8\Driver());
    }

    public function testConnectionInstance() {
        $this->assertInstanceOf('Zeedhi\Framework\DBAL\Connection', $this->connection);
    }

    public function testCreateQueryBuilder() {
        $query = $this->connection->createQueryBuilder();
        $this->assertInstanceOf('Zeedhi\Framework\DBAL\QueryBuilder', $query);
    }
}