<?php
namespace HumanRelation\Entities\Base;


abstract class JobHistory {
    
    
//    /** @var int  */
//    protected $employeeId;
    /** @var \HumanRelation\Entities\Employees  */
    protected $employee;
    /** @var \Datetime  */
    protected $startDate;
    /** @var \Datetime  */
    protected $endDate;
    /** @var \HumanRelation\Entities\Jobs  */
    protected $job;
    /** @var \HumanRelation\Entities\Departments  */
    protected $department;

//    public function getEmployeeId() {
//        return $this->employeeId;
//    }
//	public function setEmployeeId($employeeId) {
//        $this->employeeId = $employeeId;
//    }
    public function getEmployee() {
        return $this->employee;
    }
    public function setEmployee(\HumanRelation\Entities\Employees $employee) {
        $this->employee = $employee;
    }
	public function getStartDate() {
        return $this->startDate;
    }
	public function setStartDate(\Datetime $startDate) {
        $this->startDate = $startDate;
    }
	public function getEndDate() {
        return $this->endDate;
    }
	public function setEndDate(\Datetime $endDate) {
        $this->endDate = $endDate;
    }
	public function getJob() {
        return $this->job;
    }
	public function setJob(\HumanRelation\Entities\Jobs $job) {
        $this->job = $job;
    }
	public function getDepartment() {
        return $this->department;
    }
	public function setDepartment(\HumanRelation\Entities\Departments $department) {
        $this->department = $department;
    }
}