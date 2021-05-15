<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\CustomColumn;

use Zeedhi\Framework\DataSource\Manager\CustomColumn\NameProvider;

class NameProviderTest extends \PHPUnit\Framework\TestCase {

    public function testFactory() {
        $nameProvider = NameProvider::factoryFromEntitiesJSON(
            __DIR__."/../../../../../mocks/entities.json",
            __DIR__."/../../../../../mocks/src/HumanRelation/Util/gen/datasources"
        );

        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Manager\CustomColumn\NameProvider', $nameProvider);
        $config = $nameProvider->getDataSourceByName('regions');
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Manager\CustomColumn\Configuration', $config);
    }
}