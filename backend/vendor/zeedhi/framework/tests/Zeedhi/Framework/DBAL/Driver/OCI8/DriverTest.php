<?php
namespace tests\Zeedhi\Framework\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\DriverException;
use Zeedhi\Framework\DBAL\Driver\OCI8\Driver;
use Doctrine\DBAL\Driver\OCI8\OCI8Exception;

class DriverTest extends \PHPUnit_Framework_TestCase {

    /** @var Driver */
    protected $driver;

    protected function setUp() {
        $this->driver = new Driver();
    }

    public function testConnect() {
        $this->assertEquals('zeedhi_oci8', $this->driver->getName());
        $connection = $this->driver->connect(array(
            'driverClass' => '\Zeedhi\Framework\DBAL\Driver\OCI8\Driver',
            'driver'      => 'oci8',
            'host'        => '192.168.122.5',
            'port'        => '1521',
            'dbname'      => 'pdborcl',
            'service'     => true
        ), 'USR_ORG_20', 'teknisa');

        $this->assertInstanceOf('Zeedhi\Framework\DBAL\Driver\OCI8\OCI8Connection', $connection);
        $this->assertEquals(1, $connection->prepare('SELECT SYSDATE FROM DUAL')->execute());
    }

    public function testConnectException() {
        $this->setExpectedException('\Doctrine\DBAL\DBALException', 'An exception occurred in driver: ORA-01017: invalid username/password; logon denied');
        $connection = $this->driver->connect(array(
            'host'      => '192.168.122.5',
            'dbname'    => 'pdborcl',
            'driver'    => 'oci8',
            'port'      => '1521',
            'service'   => true
        ), 'USR_ORG_20', 'wrong_password');
    }

    public function testUTF8() {
        $message = "Mensagem com caracters especiais. àçãö";
        $exception = new OCI8Exception($message);

        $convertedException = $this->driver->convertException($message, $exception);

        $encodedMessage = utf8_encode($message);
        $this->assertInstanceOf('Doctrine\DBAL\Exception\DriverException', $convertedException);
        $this->assertEquals($encodedMessage, $convertedException->getMessage());
        $this->assertEquals($exception, $convertedException->getPrevious());
    }

}