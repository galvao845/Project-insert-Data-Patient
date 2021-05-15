<?php
namespace tests\Zeedhi\Framework\DBAL;

use Doctrine\DBAL\Driver\DriverException;
use Zeedhi\Framework\DBAL;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase {

    /** @var DBAL\QueryBuilder */
    protected $query;

    protected function setUp() {
        $connection = DBAL\DriverManager::getConnection(
            [
                'driverClass' => '\Zeedhi\Framework\DBAL\Driver\OCI8\Driver',
                'driver'      => 'oci8',
                'host'        => '192.168.122.5',
                'port'        => '1521',
                'user'        => 'USR_ORG_20',
                'password'    => 'teknisa',
                'dbname'      => 'pdborcl',
                'service'     => true
            ]
        );
        $this->query = $connection->createQueryBuilder();
    }

    public function testQueryBuilderInstance() {
        $this->assertInstanceOf('Zeedhi\Framework\DBAL\QueryBuilder', $this->query);
    }

    public function testSQL() {
        $query = $this->query
            ->select('FIRST_NAME, JOB_ID')
            ->from('EMPLOYEES')
            ->where('EMPLOYEE_ID = :EMPLOYEE_ID')
            ->setParameter(':EMPLOYEE_ID', 101);

        $query->execute();

        $resultSQL = $query->getSQL();

        $toMatchBegin = ' /* {"Parameters": {"EMPLOYEE_ID": 101},"StackTrace": "Zeedhi';
        $toMatchEnd = '*/ SELECT FIRST_NAME, JOB_ID FROM EMPLOYEES WHERE EMPLOYEE_ID = :EMPLOYEE_ID';

        $this->assertContains($toMatchBegin, $resultSQL);
        $this->assertContains($toMatchEnd, $resultSQL);
    }
}