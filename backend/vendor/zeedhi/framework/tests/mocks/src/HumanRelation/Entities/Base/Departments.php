<?php
namespace HumanRelation\Entities\Base;


abstract class Departments {
    
    
    /** @var int  */
    protected $departmentId;
    /** @var string  */
    protected $departmentName;
    /** @var int  */
    protected $managerId;
    /** @var \HumanRelation\Entities\Locations  */
    protected $location;
    /** @var string  */
    protected $description;

    public function getDepartmentId() {
        return $this->departmentId;
    }
	public function setDepartmentId($departmentId) {
        $this->departmentId = $departmentId;
    }
	public function getDepartmentName() {
        return $this->departmentName;
    }
	public function setDepartmentName($departmentName) {
        $this->departmentName = $departmentName;
    }
	public function getManagerId() {
        return $this->managerId;
    }
	public function setManagerId($managerId) {
        $this->managerId = $managerId;
    }
	public function getLocation() {
        return $this->location;
    }
	public function setLocation(\HumanRelation\Entities\Locations $location) {
        $this->location = $location;
    }
	public function getDescription() {
        return $this->description;
    }
	public function setDescription($description) {
        $this->description = $description;
    }
}