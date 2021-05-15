<?php
namespace Zeedhi\Framework\DataSource\Manager\IdProvider\Providers;

use Doctrine\DBAL\Connection;
use Zeedhi\Framework\DataSource\Manager\IdProvider\IdProvider;

class SqlSequence implements IdProvider {
    
    protected $connection;
    protected $sequenceName;
    
    public function __construct(Connection $connection, $sequenceName) {
        $this->connection = $connection;
        $this->sequenceName = $sequenceName;
    }
    
    public function getNextId() {
        $query = $this->connection->getDatabasePlatform()->getSequenceNextValSQL($this->sequenceName);
        $sequenceVal = $this->connection->fetchAssoc($query);

        return $sequenceVal['NEXTVAL'];
    }
}