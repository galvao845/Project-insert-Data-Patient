<?php
namespace HumanRelation\Entities\Base;


abstract class Employees {
    
    
    /** @var int  */
    protected $employeeId;
    /** @var string  */
    protected $firstName;
    /** @var string  */
    protected $lastName;
    /** @var string  */
    protected $email;
    /** @var string  */
    protected $phoneNumber;
    /** @var \Datetime  */
    protected $hireDate;
    /** @var \HumanRelation\Entities\Jobs  */
    protected $job;
    /** @var int  */
    protected $salary;
    /** @var int  */
    protected $commissionPct;
    /** @var \HumanRelation\Entities\Employees  */
    protected $manager;
    /** @var \HumanRelation\Entities\Departments  */
    protected $department;
    /** @var \Datetime  */
    protected $dataNascimento;

    public function getEmployeeId() {
        return $this->employeeId;
    }
	public function setEmployeeId($employeeId) {
        $this->employeeId = $employeeId;
    }
	public function getFirstName() {
        return $this->firstName;
    }
	public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }
	public function getLastName() {
        return $this->lastName;
    }
	public function setLastName($lastName) {
        $this->lastName = $lastName;
    }
	public function getEmail() {
        return $this->email;
    }
	public function setEmail($email) {
        $this->email = $email;
    }
	public function getPhoneNumber() {
        return $this->phoneNumber;
    }
	public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
    }
	public function getHireDate() {
        return $this->hireDate;
    }
	public function setHireDate(\Datetime $hireDate) {
        $this->hireDate = $hireDate;
    }
	public function getJob() {
        return $this->job;
    }
	public function setJob(\HumanRelation\Entities\Jobs $job) {
        $this->job = $job;
    }
	public function getSalary() {
        return $this->salary;
    }
	public function setSalary($salary) {
        $this->salary = $salary;
    }
	public function getCommissionPct() {
        return $this->commissionPct;
    }
	public function setCommissionPct($commissionPct) {
        $this->commissionPct = $commissionPct;
    }
	public function getManager() {
        return $this->manager;
    }
	public function setManager(\HumanRelation\Entities\Employees $manager) {
        $this->manager = $manager;
    }
	public function getDepartment() {
        return $this->department;
    }
	public function setDepartment(\HumanRelation\Entities\Departments $department) {
        $this->department = $department;
    }
	public function getDataNascimento() {
        return $this->dataNascimento;
    }
	public function setDataNascimento(\Datetime $dataNascimento) {
        $this->dataNascimento = $dataNascimento;
    }
}