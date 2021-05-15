<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\CustomColumn;

use Zeedhi\Framework\DataSource\Manager\CustomColumn\Configuration;
use Zeedhi\Framework\DataSource\Exception;

class ConfigurationTest extends \PHPUnit\Framework\TestCase {

    const FILE_LOCATION = __DIR__ .DIRECTORY_SEPARATOR.'datasources'.DIRECTORY_SEPARATOR;

    public function testFactory() {
        $config = Configuration::factoryFromFileLocation(self::FILE_LOCATION, 'message');
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\CustomColumn\Configuration', $config);

        $customColumns = $config->getCustomColumns();

        $this->assertCount(1, $customColumns);
        $this->assertEquals(array('date'), $customColumns);
    }

    public function testFactoryWithoutCustomColumns() {
        $config = Configuration::factoryFromFileLocation(self::FILE_LOCATION, 'topic');
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\CustomColumn\Configuration', $config);

        $customColumns = $config->getCustomColumns();

        $this->assertInternalType('array', $customColumns);
        $this->assertCount(0, $customColumns);
    }
}