<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo;

use Zeedhi\Framework\DataSource\Manager\Mongo\ManagerImpl;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Manager\Mongo\NameProvider;
use Zeedhi\Framework\DB\Mongo\Mongo;
use Zeedhi\Framework\DTO\Row;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ManagerImplTest extends \PHPUnit\Framework\TestCase {

    const DATA_SOURCE_NAME = 'mongoTest';
    const MONGO_PORT = "27019";
    const MONGO_HOST = "192.168.122.55";
    const MONGO_DB_NAME = 'lambda';
    const DATA_SOURCE_NAME_WITH_INTERNAL_COLLECTION = 'service';
    const DATA_SOURCE_NAME_INTERNAL_COLLECTION = 'service_param';

    /** @var ManagerImpl */
    protected $manager;
    /** @var Mongo */
    protected $mongo;
    /** @var bool */
    protected $extensionLoaded;
    /** @var bool */
    protected $mongoDbServerOnline = true;
    /** @var NameProvider */
    protected $nameProvider;

    protected $objects = array(
        array('_id' => '56aa7361a7b9911802e2ff30', 'key' => 0, 'text' => 'zero',    'date' => '01-01-2016', 'foreignKey' => '56aa736133da911802e2ff30', '__is_new' => false),
        array('_id' => '56aa7361a7b9911802e2ff31', 'key' => 1, 'text' => 'um',      'date' => '02-01-2016', 'foreignKey' => '56aa736133da911802e2ff31', '__is_new' => false),
        array('_id' => '56aa7361a7b9911802e2ff32', 'key' => 2, 'text' => 'dois',    'date' => '03-01-2016', 'foreignKey' => '56aa736133da911802e2ff32', '__is_new' => false),
        array('_id' => '56aa7361a7b9911802e2ff33', 'key' => 3, 'text' => 'tres',    'date' => '04-01-2016', 'foreignKey' => '56aa736133da911802e2ff33', '__is_new' => false),
        array('_id' => '56aa7361a7b9911802e2ff34', 'key' => 4, 'text' => 'quatro',  'date' => '05-01-2016', 'foreignKey' => '56aa736133da911802e2ff34', '__is_new' => false),
        array('_id' => '56aa7361a7b9911802e2ff35', 'key' => 5, 'text' => 'cinco',   'date' => '06-01-2016', 'foreignKey' => '56aa736133da911802e2ff35', '__is_new' => false),
        array('_id' => '56aa7361a7b9911802e2ff36', 'key' => 6, 'text' => 'seis',    'date' => '07-01-2016', 'foreignKey' => '56aa736133da911802e2ff36', '__is_new' => false)
    );

    protected $services = array(
        array('__is_new' => false, '_id' => '5706add1154bb4fc15000029', 'name' => 'Notification Service 1', 'serviceParams' => array(array('_id' => '570d5ffef88d05b14f95fd3d', 'name' => 'Default Icon'), array('_id' => '570d5ffef88d05b14f95fd3e', 'name' => 'Package Name'))),
        array('__is_new' => false, '_id' => '5706add1154bb4fc1500002a', 'name' => 'Notification Service 2', 'serviceParams' => array())
    );

    protected $objectsConverted = false;

    public function setUp() {
        if (!$this->objectsConverted) {
            $this->objectsConverted = true;
            $this->objects = array_map(function($object) {
                $object['date'] = \DateTime::createFromFormat('d-m-Y', $object['date']);
                return $object;
            }, $this->objects);
        }

        $this->extensionLoaded = extension_loaded('mongodb');
        if (!$this->extensionLoaded) {
            $this->markTestSkipped('The tests require mongodb extension.');
        }

        if ($this->mongoDbServerOnline) {
            try {
                $this->mongo = new Mongo(self::MONGO_HOST, self::MONGO_PORT, self::MONGO_DB_NAME);
                foreach($this->objects as $object) {
                    $object['_id']        = new ObjectId($object['_id']);
                    $object['foreignKey'] = new ObjectId($object['foreignKey']);
                    $object['date']       = new UTCDateTime($object['date']);
                    unset($object['__is_new']);
                    $this->mongo->insert(self::DATA_SOURCE_NAME, $object);
                }

                foreach ($this->services as $service) {
                    $service['_id'] = new ObjectId($service['_id']);
                    $service['serviceParams'] = array_map(function($serviceParam) {
                        $serviceParam['_id'] = new ObjectId($serviceParam['_id']);
                        return $serviceParam;
                    }, $service['serviceParams']);
                    unset($service['__is_new']);
                    $this->mongo->insert(self::DATA_SOURCE_NAME_WITH_INTERNAL_COLLECTION, $service);
                }

                $this->nameProvider = new NameProvider(__DIR__.'/datasources', '');
                $this->manager = new ManagerImpl($this->mongo, $this->nameProvider);
            } catch (\Exception $e) {
                $this->mongoDbServerOnline = false;
            }
        }

        if (!$this->mongoDbServerOnline) {
            $this->markTestSkipped('The tests require mongo db server be online');
        }
    }

    public function tearDown() {
        if ($this->extensionLoaded && $this->mongoDbServerOnline) {
            $this->mongo->dropCollection(self::DATA_SOURCE_NAME);
            $this->mongo->dropCollection(self::DATA_SOURCE_NAME_WITH_INTERNAL_COLLECTION);
        }
    }

    public function testFindAll() {
        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $managerResult = $this->manager->findBy($filterCriteria);
        $this->assertEquals($managerResult->getRows(), $this->objects);
    }

    public function testSave() {
        $rows = array(
            array('key' => 7, 'text' => 'sete', 'date' => new \DateTime('08-01-2016'), 'foreignKey' => '56aa736133da911802e2ff37', '__is_new' => false),
            array('key' => 8, 'text' => 'oito', 'date' => new \DateTime('09-01-2016'), 'foreignKey' => '56aa736133da911802e2ff38', '__is_new' => false)
        );
        $rows[0]['__is_new'] = true;
        $rows[1]['__is_new'] = true;

        $data = array(
            new Row($rows[0]),
            new Row($rows[1])
        );

        $dataSource = new DataSet(self::DATA_SOURCE_NAME, $data);
        $rowsInsertedIds = $this->manager->persist($dataSource);

        foreach ($rowsInsertedIds as $key => $value) {
            $rows[$key]['_id'] = $value['_id'];
            $rows[$key]['__is_new'] = false;
        }

        $expected = array_merge($this->objects, $rows);

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $result = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $result->getRows());
    }


    public function testFindById() {
        $expected = array_values(array_filter($this->objects, function($obj) {
            return $obj['_id'] == '56aa7361a7b9911802e2ff33';
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('_id', '56aa7361a7b9911802e2ff33');

        $result = $this->manager->findBy($filterCriteria);
        $this->assertEquals($expected, $result->getRows());
    }

    public function testFindEqualsTo() {
        $expected = array_values(array_filter($this->objects, function($obj) {
            return $obj['key'] == 0;
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('key', 0);
        $result = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $result->getRows());
    }

    public function testFindLessThan() {
        $expected = array_filter($this->objects, function($obj) {
            return $obj['key'] < 3;
        });

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('key', '<', 3);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    public function testFindLessOrEqualsThan() {
        $expected = array_filter($this->objects, function($obj) {
            return $obj['key'] <= 3;
        });

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('key', '<=', 3);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    public function testFindGreaterThan() {
        $expected = array_values(array_filter($this->objects, function($obj) {
            return $obj['key'] > 3;
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('key', '>', 3);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    public function testFindGreaterOrEqualsThan() {
        $expected = array_values(array_filter($this->objects, function($obj) {
            return $obj['key'] >= 3;
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('key', '>=', 3);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    public function testUpdate() {
        $newRow = array('_id' => '56aa7361a7b9911802e2ff30', 'key' => 10, 'text' => 'dez', 'date' => new \DateTime('10-01-2016'), 'foreignKey' => '56aa736133da911802e2ff30', '__is_new' => false);

        $expected = $this->objects;
        $expected[0] = $newRow;

        $data = array(
            new Row($newRow)
        );

        $dataSet = new DataSet(self::DATA_SOURCE_NAME, $data);
        $this->manager->persist($dataSet);

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $result = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $result->getRows());
    }

    public function testDelete() {
        $expected = array_values(array_filter($this->objects, function($obj) {
            return $obj['key'] != 0;
        }));

        $deleteRow = array('_id' => '56aa7361a7b9911802e2ff30', 'key' => 0, 'text' => 'zero', 'foreignKey' => '56aa736133da911802e2ff30', '__is_new' => false);
        $data = array(
            new Row($deleteRow)
        );
        $dataSet = new DataSet(self::DATA_SOURCE_NAME, $data);
        $this->manager->delete($dataSet);

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $result = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $result->getRows());
    }

    public function testFindByIdInArray() {
        $ids = array(
            '56aa7361a7b9911802e2ff30',
            '56aa7361a7b9911802e2ff32',
            '56aa7361a7b9911802e2ff34',
            '56aa7361a7b9911802e2ff36'
        );

        $expected = array_values(array_filter($this->objects, function($obj) use (&$ids) {
            return in_array($obj['_id'], $ids);
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('_id', 'IN', $ids);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    /**
     * @expectedException TypeError
     */
    public function testFindIn() {
        $value = 1;
        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('_id', 'IN', $value);
        $this->manager->findBy($filterCriteria);
    }

    public function testFindByIdNotInArray() {
        $ids = array(
            '56aa7361a7b9911802e2ff30',
            '56aa7361a7b9911802e2ff32',
            '56aa7361a7b9911802e2ff34',
            '56aa7361a7b9911802e2ff36'
        );

        $expected = array_values(array_filter($this->objects, function($obj) use (&$ids) {
            return !in_array($obj['_id'], $ids);
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('_id', 'NOT_IN', $ids);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    /**
     * @expectedException TypeError
     */
    public function testFindNin() {
        $value = '1';
        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('_id', 'NOT_IN', $value);
        $this->manager->findBy($filterCriteria);
    }

    public function testFindKeyBetween() {
        $expected = array_values(array_filter($this->objects, function($obj) {
            return $obj['key'] >= 1 && $obj['key'] <= 5;
        }));

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $filterCriteria->addCondition('key', 'BETWEEN', [1, 5]);
        $managerResult = $this->manager->findBy($filterCriteria);

        $this->assertEquals($expected, $managerResult->getRows());
    }

    public function testInsertWithInternalCollection() {
        $rows = array(
            array('__is_new' => true, 'name' => 'Google Cloud Messaging',          'serviceParams' => array(array('name' => 'RegistrationToken'))),
            array('__is_new' => true, 'name' => 'Apple Push Notification Service', 'serviceParams' => array())
        );
        $dataSet = new DataSet(self::DATA_SOURCE_NAME_WITH_INTERNAL_COLLECTION, $rows);
        $rowsInsertedIds = $this->manager->persist($dataSet);

        foreach ($rowsInsertedIds as $key => $value) {
            $rows[$key]['_id'] = $value['_id'];
            $rows[$key]['__is_new'] = false;

            foreach ($rows[$key]['serviceParams'] as $paramKey => $valueValue) {
                $rows[$key]['serviceParams'][$paramKey]['_id'] = $value['serviceParams'][$paramKey]['_id'];
            }
        }

        $filterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME_WITH_INTERNAL_COLLECTION);
        $result = $this->manager->findBy($filterCriteria);

        $expected = array_merge($this->services, $rows);
        $this->assertCount(4, $result->getRows());
        $this->assertEquals($expected, $result->getRows());
    }

    public function testFindInInternalCollection() {
        $filter = new FilterCriteria(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION);
        $filter->addCondition('serviceId', '5706add1154bb4fc15000029');
        $filter->addCondition('name', 'Package Name');
        $result = $this->manager->findBy($filter);
        $rows = $result->getRows();

        $expected = array(array('_id' => '570d5ffef88d05b14f95fd3e', 'name' => 'Package Name', '__is_new' => false));

        $this->assertCount(1, $rows);
        $this->assertEquals($expected, $rows);
    }

    public function testFindInInternalCollectionNotFoundingExternal() {
        $filter = new FilterCriteria(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION);
        $filter->addCondition('serviceId', '000000000000000000000000');
        $filter->addCondition('name', 'Package Name');
        $result = $this->manager->findBy($filter);
        $rows = $result->getRows();

        $this->assertEmpty($rows);
    }

    public function testInsertInInternalCollection() {
        $rows = array(
            array('__is_new' => true, '_id' => '5706bcdb154bb4381600005a', 'name' => 'API Key', 'serviceId' => '5706add1154bb4fc15000029')
        );
        $dataSet = new DataSet(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION, $rows);
        $this->manager->persist($dataSet);

        $filter = new FilterCriteria(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION);
        $filter->addCondition('serviceId', '5706add1154bb4fc15000029');
        $result = $this->manager->findBy($filter);
        $rows = $result->getRows();

        $expected = array(
            array('_id' => '570d5ffef88d05b14f95fd3d', 'name' => 'Default Icon', '__is_new' => false),
            array('_id' => '570d5ffef88d05b14f95fd3e', 'name' => 'Package Name', '__is_new' => false),
            array('_id' => '5706bcdb154bb4381600005a', 'name' => 'API Key', '__is_new' => false)
        );
        $this->assertCount(3, $rows);
        $this->assertEquals($expected, $rows);
    }

    public function testUpdateInInternalCollection() {
        $rows = array(
            array('__is_new' => false, '_id' => '570d5ffef88d05b14f95fd3e', 'name' => 'JAVA Package Name', 'serviceId' => '5706add1154bb4fc15000029')
        );
        $dataSet = new DataSet(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION, $rows);
        $this->manager->persist($dataSet);

        $filter = new FilterCriteria(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION);
        $filter->addCondition('serviceId', '5706add1154bb4fc15000029');
        $result = $this->manager->findBy($filter);
        $rows = $result->getRows();

        $expected = array(
            array('_id' => '570d5ffef88d05b14f95fd3d', 'name' => 'Default Icon', '__is_new' => false),
            array('_id' => '570d5ffef88d05b14f95fd3e', 'name' => 'JAVA Package Name', '__is_new' => false),
        );
        $this->assertCount(2, $rows);
        $this->assertSame($expected, $rows);
    }

    public function testDeleteInInternalCollection() {
        $rows = array(
            array('_id' => '570d5ffef88d05b14f95fd3d', 'serviceId' => '5706add1154bb4fc15000029')
        );
        $dataSet = new DataSet(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION, $rows);
        $this->manager->delete($dataSet);

        $filter = new FilterCriteria(self::DATA_SOURCE_NAME_INTERNAL_COLLECTION);
        $filter->addCondition('serviceId', '5706add1154bb4fc15000029');
        $result = $this->manager->findBy($filter);
        $rows = $result->getRows();

        $expected = array(
            array('_id' => '570d5ffef88d05b14f95fd3e', 'name' => 'Package Name', '__is_new' => false)
        );
        $this->assertCount(1, $rows);
        $this->assertSame($expected, $rows);
    }

    public function testPopulateDataSet(){
        $dataSet = new DataSet(self::DATA_SOURCE_NAME, array(
            new Row(array('column' => array('__ALL' => [])))
        ));
        $expected = $this->objects;
        $expectedResult = new DataSet(self::DATA_SOURCE_NAME, array(
            new Row(array(
                'column' => $expected
            ))
        ));

        $finalResult = $this->manager->populateDataSet($dataSet);

        $this->assertEquals($expectedResult, $finalResult);
    }

    public function testBuildAllDataFilter(){
        $value = array('__ALL' => array(array('name' => '_id', 'operator' => '=', 'value' => 1)));
        $rows = array(array('column_EXCEPT' => array(4), 'column' => $value));
        $expectedFilterCriteria = new FilterCriteria(self::DATA_SOURCE_NAME);
        $expectedFilterCriteria->addCondition('column', "NOT_IN", array(4));
        $expectedFilterCriteria->addCondition('_id', '=', 1);
        $finalResult = $this->manager->buildAllDataFilter($rows, $value, 'column', self::DATA_SOURCE_NAME);

        $this->assertEquals($expectedFilterCriteria, $finalResult);
    }

}