<?php
namespace tests\Zeedhi\Framework\ORM;

use Zeedhi\Framework\ORM\ConnectionFactory;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase {


    public function testEncryptAndDecrypt(){
        $salt = 'rt6hHy8jajjaA';
        $originalString = 'originalString';
        $encryptedString = ConnectionFactory::encrypt($originalString, $salt);
        $decryptedString = ConnectionFactory::decrypt($encryptedString, $salt);
        $this->assertEquals($originalString, $decryptedString, 'Decrypted string must be the equal to original string.');
    }

    public function testFactory() {
        $salt = 'rt6hHy8jajjaA';      
        $this->markTestSkipped('Password in DB is encryped with Crypt version 1.0.0');
        $connection = ConnectionFactory::factoryWithEncryptedPassword(array(
            'driverClass' => '\Zeedhi\Framework\DBAL\Driver\OCI8\Driver',
            'driver'      => 'oci8',
            'host'        => '192.168.122.5',
            'port'        => '1521',
            'user'        => 'USR_ORG_20',
            'password'    => 'F60oiadefIESOskvGANW39GhTJyVeHyBSkzfI1OzNbc=',
            'dbname'      => 'pdborcl',
            'service'     => true
        ));

        $this->assertInstanceOf('\\Doctrine\\DBAL\\Connection', $connection);
        $column = $connection->fetchColumn('SELECT 1 FROM DUAL');
        $this->assertEquals('1', $column);
    }

}
