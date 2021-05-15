<?php
namespace tests\Zeedhi\Framework\Util;

use HumanRelation\Entities\Countries;
use HumanRelation\Entities\Regions;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\Handler\PhpCollectionHandler;
use JMS\Serializer\Handler\PropelCollectionHandler;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Zeedhi\Framework\Serializer\Serializer;
use Zeedhi\Framework\Serializer\Strategy\Exclusion;
use Zeedhi\Framework\Serializer\Strategy\UpperCaseNaming;

class SerializerTest extends \PHPUnit\Framework\TestCase {

	/** @var Serializer */
	protected $serializer;
	/** @var Countries */
	protected $countries;

	public function setUp() {
		$serializerBuilder = new SerializerBuilder();
		$serializerContext = new SerializationContext();
		$exclusionStrategy = new Exclusion();
		$this->serializer = new Serializer($serializerContext, $serializerBuilder, $exclusionStrategy);
		$this->serializer->setNamingStrategy(new UpperCaseNaming());
		$this->serializer->addHandle(new DateHandler());
		$this->serializer->addHandle(new PhpCollectionHandler());
		$this->serializer->addHandle(new ArrayCollectionHandler());
		$this->serializer->addHandle(new PropelCollectionHandler());
		$this->prepareData();
		$this->serializer->addExclusionProperty('countryName');
		$this->serializer->addExclusionClass(get_class($this->countries->getRegion()));
	}

	private function prepareData() {
		$this->countries = new Countries();
		$this->countries->setCountryId(1);
		$this->countries->setCountryName('test');
		$regions = new Regions();
		$regions->setRegionId(1);
		$regions->setRegionName('test2');
		$this->countries->setRegion($regions);
	}

	public function testSerialize() {
		$data = $this->serializer->serialize($this->countries);
		$this->assertJson($data, 'Return must be json');
		$countries = json_decode($data, true);
		$this->assertArrayHasKey('COUNTRY_ID', $countries);
		$this->assertArrayNotHasKey('COUNTRY_NAME', $countries);
		$this->assertArrayNotHasKey('REGIONS', $countries);
    }
}
