<?php
namespace tests\Zeedhi\Framework\DBAL;

use Doctrine\DBAL\Driver\DriverException;
use Zeedhi\Framework\DBAL;

class DriverManagerTest extends \PHPUnit_Framework_TestCase {

    public function testGetConnection() {
        $connection = DBAL\DriverManager::getConnection(['driver'=>'oci8']);
        $this->assertInstanceOf('Zeedhi\Framework\DBAL\Connection', $connection);
    }
}