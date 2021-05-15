<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo;

use Zeedhi\Framework\DataSource\Manager\Mongo\Configuration;
use Zeedhi\Framework\DataSource\Manager\Mongo\Exception;

class ConfigurationTest extends \PHPUnit\Framework\TestCase {

    protected $fileLocation = __DIR__ . DIRECTORY_SEPARATOR . 'datasources' . DIRECTORY_SEPARATOR;

    public function testFactory() {
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'service');
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Configuration', $config);

        $typesMapping = array(
            '_id'           => 'mongoId',
            'name'          => 'default',
            'serviceParams' => 'collection'
        );
        $this->assertEquals($typesMapping, $config->getTypesMapping());
        $this->assertCount(1, $config->getInternalCollections());
        $this->assertContainsOnlyInstancesOf('Zeedhi\Framework\DataSource\Manager\Mongo\Configuration', $config->getInternalCollections());

        $collectionOptions = array(
            'fieldName' => 'serviceParams',
            'primaryKeysMapping' => array('_id' => 'serviceId')
        );
        $this->assertEquals($collectionOptions, $config->getInternalCollectionOptions('service_param'));
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Configuration', $config->getInternalCollectionForField('serviceParams'));
        try {
            $exception = null;
            $this->assertNull($config->getInternalCollectionForField('inexistentField'));
        } catch (Exception $e) {
            $exception = $e;
        }

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Exception', $exception, "Missing internal collection field 'inexistentField'.");
        $config = Configuration::factoryFromFileLocation($this->fileLocation, 'service_param');
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Configuration', $config->getWrapperCollection());
    }
}