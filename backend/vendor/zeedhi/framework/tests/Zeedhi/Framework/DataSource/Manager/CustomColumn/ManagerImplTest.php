<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\CustomColumn;

use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\Manager;
use Zeedhi\Framework\DataSource\Manager\CustomColumn\NameProvider;
use Zeedhi\Framework\DataSource\Manager\CustomColumn\ManagerImpl;
use Zeedhi\Framework\DataSource\Manager\CustomColumn\RequestProvider;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\DTO\Row;

class ManagerImplTest extends \PHPUnit\Framework\TestCase {

    const USER_ID               = 'userId';
    const ROUTE                 = 'route';
    const DATA_SOURCE_DIRECTORY = __DIR__.DIRECTORY_SEPARATOR . 'datasources' . DIRECTORY_SEPARATOR;

    protected $nameProvider;
    protected $originalManager;
    protected $managerForCustomColumns;
    protected $dataSourceManager;

    public function setUp() {
        $this->nameProvider = new NameProvider(self::DATA_SOURCE_DIRECTORY, "Model", array(), false);
        $this->originalManager = $this->getMockBuilder(Manager::class)
                                      ->setMethods(array('persist', 'delete', 'findBy', 'populateDataSet', 'buildAllDataFilter'))
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->managerForCustomColumns = $this->getMockBuilder(Manager::class)
                                              ->setMethods(array('persist', 'delete', 'findBy', 'populateDataSet', 'buildAllDataFilter'))
                                              ->disableOriginalConstructor()
                                              ->getMock();
        $this->dataSourceManager = new ManagerImpl($this->nameProvider, $this->originalManager, $this->managerForCustomColumns);
    }

    public function testFindBy() {
        $expectedFilterCriteria = new FilterCriteria('message');
        $expectedFilterCriteria->addCondition('author', FilterCriteria::EQ, 'Author 1');

        $messages = array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz'))
        );
        $dataSetReturn = new DataSet('message', $messages);
        $this->originalManager->expects($this->once())
                              ->method('findBy')
                              ->with($expectedFilterCriteria)
                              ->willReturn($dataSetReturn);

        $expectedFilterForCustomColumn = new FilterCriteria('customColumn');
        $expectedFilterForCustomColumn->addCondition('dataSourceName', FilterCriteria::EQ, 'message');
        $expectedFilterForCustomColumn->addCondition('key', FilterCriteria::IN, array('{"_id":"0"}', '{"_id":"2"}'));

        $this->managerForCustomColumns->expects($this->once())
                                      ->method('findBy')
                                      ->with($expectedFilterForCustomColumn)
                                      ->willReturn(new DataSet('customColumns',
                                            array(
                                                new Row(array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"0"}', 'value' => '2016-10-14 10:20:35')),
                                                new Row(array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"2"}', 'value' => '2016-10-14 10:20:55'))
                                            )
                                        ));

        $filterCriteria = new FilterCriteria('message');
        $filterCriteria->addCondition('author', FilterCriteria::EQ, 'Author 1');
        $finalResult = $this->dataSourceManager->findBy($filterCriteria);

        $expectedResult = new DataSet('message', array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        ));
        $this->assertEquals($expectedResult, $finalResult);
    }

    public function testDelete() {
        $expectedDataSet = array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo')),
            new Row(array('__is_new' => false, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz'))
        );
        $expectedDataSet = new DataSet('message', $expectedDataSet);

        $dataSetReturn = new DataSet('message', array(
            array('_id' => '0'), array('_id' => '1'), array('_id' => '2')
        ));

        $this->originalManager->expects($this->once())
                              ->method('delete')
                              ->with($expectedDataSet)
                              ->willReturn($dataSetReturn);

        $expectedDataSetForCustomColumn = new DataSet('customColumn', array(
            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"0"}'),
            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"1"}'),
            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"2"}')
        ));

        $this->managerForCustomColumns->expects($this->once())
                                      ->method('delete')
                                      ->with($expectedDataSetForCustomColumn)
                                      ->willReturn(array(
                                            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"0"}'),
                                            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"1"}'),
                                            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"2"}')
                                        ));

        $rows = array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => false, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $dataSet = new DataSet('message', $rows);
        $this->dataSourceManager->delete($dataSet);
    }

    public function testPersist() {
        $expectedDataSet = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz'))
        );
        $expectedDataSet = new DataSet('message', $expectedDataSet);

        $this->originalManager->expects($this->once())
                              ->method('persist')
                              ->with($expectedDataSet)
                              ->willReturn(array(
                                    array('_id' => '0'),
                                    array('_id' => '1'),
                                    array('_id' => '2')
                                ));

        $expectedDataSetForCustomColumn = new DataSet('customColumn', array(
            array('__is_new' => true, 'dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"0"}', 'value' => '2016-10-14 10:20:35'),
            array('__is_new' => true, 'dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"1"}', 'value' => '2016-10-14 10:20:45'),
            array('__is_new' => true, 'dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"2"}', 'value' => '2016-10-14 10:20:55')
        ));

        $this->managerForCustomColumns->expects($this->once())
                                      ->method('persist')
                                      ->with($expectedDataSetForCustomColumn)
                                      ->willReturn(array(
                                            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"0"}'),
                                            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"1"}'),
                                            array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"2"}')
                                        ));

        $rows = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $dataSet = new DataSet('message', $rows);
        $this->dataSourceManager->persist($dataSet);
    }

    public function testPopulateDataSet(){
        $expectedFilterCriteria = new FilterCriteria('message');
        $messages = array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz'))
        );
        $dataSetReturn = new DataSet('message', $messages);
        $this->originalManager->expects($this->once())
                              ->method('findBy')
                              ->with($expectedFilterCriteria)
                              ->willReturn($dataSetReturn);

        $expectedFilterForCustomColumn = new FilterCriteria('customColumn');
        $expectedFilterForCustomColumn->addCondition('dataSourceName', FilterCriteria::EQ, 'message');
        $expectedFilterForCustomColumn->addCondition('key', FilterCriteria::IN, array('{"_id":"0"}', '{"_id":"2"}'));

        $this->managerForCustomColumns->expects($this->once())
                                      ->method('findBy')
                                      ->with($expectedFilterForCustomColumn)
                                      ->willReturn(new DataSet('customColumns',
                                            array(
                                                new Row(array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"0"}', 'value' => '2016-10-14 10:20:35')),
                                                new Row(array('dataSourceName' => 'message', 'columnName' => 'date', 'key' => '{"_id":"2"}', 'value' => '2016-10-14 10:20:55'))
                                            )
                                        ));

        $dataSet = new DataSet('message', array(
            new Row(array('column' => array('__ALL' => [])))
        ));

        $expectedResult = new DataSet('message', array(
            new Row(array(
                'column' => array(
                    new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
                    new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
                )
            ))
        ));

        $finalResult = $this->dataSourceManager->populateDataSet($dataSet);

        $this->assertEquals($expectedResult, $finalResult);
    }

    public function testBuildAllDataFilter(){
        $value = array('__ALL' => array(array('name' => 'Foo', 'operator' => 'EQUALS', 'value' => 'Baz')));
        $rows = array(array('column_EXCEPT' => array('messages'), 'column' => $value));

        $expectedFilterCriteria = new FilterCriteria('messages');
        $expectedFilterCriteria->addCondition('column', "NOT_IN", array('messages'));
        $expectedFilterCriteria->addCondition('Foo', "EQUALS", 'Baz');
        $finalResult = $this->dataSourceManager->buildAllDataFilter($rows, $value, 'column', 'messages');

        $this->assertEquals($expectedFilterCriteria, $finalResult);
    }

}