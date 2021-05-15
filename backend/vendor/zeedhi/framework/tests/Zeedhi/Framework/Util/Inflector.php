<?php
namespace tests\Zeedhi\Framework\Util;

use Zeedhi\Framework\Util\DefaultInflector;

class Inflector extends DefaultInflector{

    protected $renamedTables = array(
        "COUNTRIES"   => "COUNTRY",
        "DEPARTMENTS" => "DEPARTMENT",
        "EMPLOYEES"   => "EMPLOYEE",
        "JOBS"        => "JOB",
        "LOCATIONS"   => "LOCATION",
        "REGIONS"     => "REGION"
    );

    public function classify($tableName) {
        if (isset($this->renamedTables[$tableName])) {
            $tableName = $this->renamedTables[$tableName];
        }
        
        return parent::classify($tableName);
    }
}