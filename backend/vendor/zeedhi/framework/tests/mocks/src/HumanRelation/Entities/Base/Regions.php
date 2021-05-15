<?php
namespace HumanRelation\Entities\Base;


abstract class Regions {
    
    
    /** @var int  */
    protected $regionId;
    /** @var string  */
    protected $regionName;

    public function getRegionId() {
        return $this->regionId;
    }
	public function setRegionId($regionId) {
        $this->regionId = $regionId;
    }
	public function getRegionName() {
        return $this->regionName;
    }
	public function setRegionName($regionName) {
        $this->regionName = $regionName;
    }
}