<?php
namespace Zeedhi\Framework\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager as DoctrineDriverManager;

class DriverManager{

    private const DEFAULT_DRIVER = 'Zeedhi\Framework\DBAL\Driver\OCI8\Driver';

    /**
     * @static getConnection
     *
     * Builds the connection object from the parameters.
     *
     * @param   array          $connection_params    The database connection parameters.
     * @param   Configuration  $config               The doctrine's database config object.
     * @param   EventManager   $eventManager         The doctrine's eventManager object.
     *
     * @return  Connection The connection object.
     */
    public static function getConnection(array $params, Configuration $config = null, EventManager $eventManager = null){
        $className = $params['driverClass'] ?? self::DEFAULT_DRIVER;
        $driver = new $className();
        return new Connection($params, $driver, $config, $eventManager);
    }
    
}