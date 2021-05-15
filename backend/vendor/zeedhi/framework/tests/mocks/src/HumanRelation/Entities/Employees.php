<?php
namespace HumanRelation\Entities;


class Employees extends \HumanRelation\Entities\Base\Employees {

    public function __toString() {
        return $this->employeeId."";
    }
}