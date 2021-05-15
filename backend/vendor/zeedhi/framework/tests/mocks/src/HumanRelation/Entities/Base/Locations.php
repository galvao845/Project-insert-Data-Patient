<?php
namespace HumanRelation\Entities\Base;


abstract class Locations {
    
    
    /** @var int  */
    protected $locationId;
    /** @var string  */
    protected $streetAddress;
    /** @var string  */
    protected $postalCode;
    /** @var string  */
    protected $city;
    /** @var string  */
    protected $stateProvince;
    /** @var \HumanRelation\Entities\Countries  */
    protected $country;

    public function getLocationId() {
        return $this->locationId;
    }
	public function setLocationId($locationId) {
        $this->locationId = $locationId;
    }
	public function getStreetAddress() {
        return $this->streetAddress;
    }
	public function setStreetAddress($streetAddress) {
        $this->streetAddress = $streetAddress;
    }
	public function getPostalCode() {
        return $this->postalCode;
    }
	public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
    }
	public function getCity() {
        return $this->city;
    }
	public function setCity($city) {
        $this->city = $city;
    }
	public function getStateProvince() {
        return $this->stateProvince;
    }
	public function setStateProvince($stateProvince) {
        $this->stateProvince = $stateProvince;
    }
	public function getCountry() {
        return $this->country;
    }
	public function setCountry(\HumanRelation\Entities\Countries $country) {
        $this->country = $country;
    }
}