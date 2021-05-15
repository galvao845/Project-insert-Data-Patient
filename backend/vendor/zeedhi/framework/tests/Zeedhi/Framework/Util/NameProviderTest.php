<?php
namespace tests\Zeedhi\Framework\Util;

use Zeedhi\Framework\Util\NameProvider;

class NameProviderTest extends \PHPUnit\Framework\TestCase {

    public function testFactoryDefault() {
        $nameProvider = NameProvider::factoryDefault();
        $this->assertInstanceOf('\Zeedhi\Framework\Util\NameProvider', $nameProvider);
        $this->assertEquals('\Model\Lambda', $nameProvider->getClassName('LAMBDA'));
    }

    public function testFactory() {
        $nameProvider = NameProvider::factoryFromEntitiesJSON(
            __DIR__."/../../../mocks/entities.json",
            __DIR__."/../../../mocks/src/HumanRelation/Util/gen/datasources"
        );

        $this->assertInstanceOf('\Zeedhi\Framework\Util\NameProvider', $nameProvider);
        $this->assertEquals('\HumanRelation\Entities\Regions', $nameProvider->getClassName('REGIONS'));
        $config = $nameProvider->getDataSourceByName('regions');
        $this->assertEquals('REGIONS', $config->getTableName());
    }

    public function testFactoryWithInflector() {
        $nameProvider = NameProvider::factoryFromEntitiesJSON(realpath(__DIR__."/entities.json"), __DIR__);
        $this->assertInstanceOf('\Zeedhi\Framework\Util\NameProvider', $nameProvider);
        $this->assertEquals("\\HumanRelation\\Entities\\Country", $nameProvider->getClassName("COUNTRIES"));
    }
}
