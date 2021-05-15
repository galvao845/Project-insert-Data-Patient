<?php
namespace HumanRelation\Entities\Base;


abstract class Jobs {
    
    
    /** @var string  */
    protected $jobId;
    /** @var string  */
    protected $jobTitle;
    /** @var int  */
    protected $minSalary;
    /** @var int  */
    protected $maxSalary;

    public function getJobId() {
        return $this->jobId;
    }
	public function setJobId($jobId) {
        $this->jobId = $jobId;
    }
	public function getJobTitle() {
        return $this->jobTitle;
    }
	public function setJobTitle($jobTitle) {
        $this->jobTitle = $jobTitle;
    }
	public function getMinSalary() {
        return $this->minSalary;
    }
	public function setMinSalary($minSalary) {
        $this->minSalary = $minSalary;
    }
	public function getMaxSalary() {
        return $this->maxSalary;
    }
	public function setMaxSalary($maxSalary) {
        $this->maxSalary = $maxSalary;
    }
}