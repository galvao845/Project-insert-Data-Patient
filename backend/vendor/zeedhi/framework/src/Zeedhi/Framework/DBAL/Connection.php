<?php
namespace Zeedhi\Framework\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as DoctrineConnection;

class Connection extends DoctrineConnection {

    /**
     * {@inheritdoc}
     */
    public function __construct(array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null){
        parent::__construct($params, $driver, $config, $eventManager);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder(){
        return new QueryBuilder($this);
    }
    
}