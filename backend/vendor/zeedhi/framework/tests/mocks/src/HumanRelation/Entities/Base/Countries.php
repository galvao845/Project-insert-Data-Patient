<?php
namespace HumanRelation\Entities\Base;


abstract class Countries {
    
    
    /** @var string  */
    protected $countryId;
    /** @var string  */
    protected $countryName;
    /** @var \HumanRelation\Entities\Regions  */
    protected $region;

    public function getCountryId() {
        return $this->countryId;
    }
	public function setCountryId($countryId) {
        $this->countryId = $countryId;
    }
	public function getCountryName() {
        return $this->countryName;
    }
	public function setCountryName($countryName) {
        $this->countryName = $countryName;
    }
	public function getRegion() {
        return $this->region;
    }
	public function setRegion(\HumanRelation\Entities\Regions $region) {
        $this->region = $region;
    }
}