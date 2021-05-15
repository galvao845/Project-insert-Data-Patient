<?php
namespace HumanRelation\Util;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class EntityManagerFactory {

    /**
     * @param $dbParams
     * @param $isDevMode
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    protected static function createFromConnectionParams($dbParams, $isDevMode = false) {
        $paths = array(realpath(__DIR__ . "/../../../dcm/"));
        $config = Setup::createXMLMetadataConfiguration($paths, $isDevMode);
        $config->setProxyDir(__DIR__ . '/../Proxies');
        return EntityManager::create($dbParams, $config);
    }

    /**
     * @param bool $isDevMode
     * @return EntityManager
     */
    public static function createWithOracleConnection($isDevMode = false) {
        $dbParams = array(
            'driver'    => 'oci8',
            'host'      => '192.168.122.5',
            'port'      => '1521',
            'user'      => 'USR_ORG_20',
            'password'  => 'teknisa',
            'dbname'    => 'pdborcl',
            'service'   => true
        );

        $entityManager = self::createFromConnectionParams($dbParams, $isDevMode);
        $eventManager = $entityManager->getEventManager();
        $eventManager->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\OracleSessionInit());
        return $entityManager;
    }

    /**
     * @param bool $isDevMode
     * @return EntityManager
     */
    public static function createSqlServerEntityManager($isDevMode = false) {
        $dbParams = array(
            'driver'    => 'sqlsrv',
            'user'      => 'sa',
            'password'  => 'Zeedh1@ds13',
            'host'      => '192.168.120.32',
            'dbname'    => 'ZEEDHI_DEMO',
            'service'   => true
        );

        return self::createFromConnectionParams($dbParams, $isDevMode);
    }

}