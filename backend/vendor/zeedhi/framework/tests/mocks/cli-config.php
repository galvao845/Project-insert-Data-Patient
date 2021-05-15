<?php
require_once __DIR__."/../bootstrap.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = array(realpath(__DIR__."/dcm"));
$isDevMode = false;

// the connection configuration
$dbParams = array(
    'driver'    => 'oci8',
    'host'      => '192.168.122.155',
    'port'      => '1521',
    'user'      => 'USR_ORG_20',
    'password'  => 'teknisa',
    'dbname'    => 'pdborcl',
    'service'   => true
);

$config = Setup::createXMLMetadataConfiguration($paths, $isDevMode);
$config->setProxyDir(__DIR__ . '/src/HumanRelation/Proxies');

$entityManager = EntityManager::create($dbParams, $config);

$eventManager = $entityManager->getEventManager();
$eventManager->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\OracleSessionInit());

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);