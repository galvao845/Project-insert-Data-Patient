<?php
namespace Zeedhi\Framework\DBAL\Driver\OCI8;

class OCI8Connection extends \Doctrine\DBAL\Driver\OCI8\OCI8Connection {

    public function prepare($prepareString) {
        return new OCI8Statement($this->dbh, $prepareString, $this);
    }

}