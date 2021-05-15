<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Remote;

use Zeedhi\Framework\DTO\Response\Error;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\Manager\Remote\Exception;
use Zeedhi\Framework\DataSource\Manager\Remote\ManagerImpl;
use Zeedhi\Framework\DataSource\Manager\Remote\RequestProvider;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\Remote\Server;
use Zeedhi\Framework\Remote\RequestFactory;

class ManagerImplTest extends \PHPUnit\Framework\TestCase {

    const USER_ID     = 'userId';
    const ROUTE       = 'route';
    const HTTP_METHOD = 'POST';

    protected $dataSourceManager;
    protected $requestFactory;
    protected $remoteServer;

    public function setUp() {
        $this->requestFactory = new RequestFactory();
        $this->remoteServer   = $this->getMockBuilder(Server::class)
                                     ->setMethods(array('request'))
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->requestProvider = $this->getMockBuilder(RequestProvider::class)
                                      ->setMethods(array('getRequest'))
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->dataSourceManager = new ManagerImpl($this->remoteServer, $this->requestFactory, $this->requestProvider);
    }

    public function testFindBy() {
        $filterCriteria = new FilterCriteria('message');
        $filterCriteria->addCondition('field', FilterCriteria::EQ, 'value');

        $method = 'POST';
        $request = new Request\Filter($filterCriteria, $method, self::ROUTE, self::USER_ID);
        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));
        $request = new Request\Filter($filterCriteria, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $expectedMessages = array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => false, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $expectedDataSet = new DataSet('message', $expectedMessages);
        $response = new Response();
        $response->addDataSet($expectedDataSet);

        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));

        $this->remoteServer->expects($this->once())
                           ->method('request')
                           ->with($request)
                           ->will($this->returnValue($response));

        $remoteDataSet = $this->dataSourceManager->findBy($request->getFilterCriteria());

        $this->assertEquals($expectedDataSet, $remoteDataSet);
    }

    public function testInsert() {
        $messages = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $dataSet = new DataSet('message', $messages);

        $request = new Request\DataSet($dataSet, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));

        $expectedMessages = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $expectedDataSet = new DataSet('message', $expectedMessages);
        $expectedRequest = new Request\DataSet($expectedDataSet, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $response = new Response();
        $response->addDataSet($expectedDataSet);
        $this->remoteServer->expects($this->once())
                           ->method('request')
                           ->with($expectedRequest)
                           ->will($this->returnValue($response));

        $remoteDataSet = $this->dataSourceManager->persist($request->getDataSet());

        $expectedReturn = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $this->assertEquals($expectedReturn, $remoteDataSet);

        $this->assertEquals(array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        ), $request->getDataSet()->getRows());
    }

    public function testDelete() {
        $messages = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $dataSet = new DataSet('message', $messages);

        $request = new Request\DataSet($dataSet, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));

        $expectedMessages = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $expectedDataSet = new DataSet('message', $expectedMessages);
        $expectedRequest = new Request\DataSet($expectedDataSet, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $response = new Response();
        $response->addDataSet($expectedDataSet);
        $this->remoteServer->expects($this->once())
                           ->method('request')
                           ->with($expectedRequest)
                           ->will($this->returnValue($response));

        $remoteDataSet = $this->dataSourceManager->delete($request->getDataSet());

        $expectedReturn = array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $this->assertEquals($expectedReturn, $remoteDataSet);

        $this->assertEquals(array(
            new Row(array('__is_new' => true, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => true, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => true, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        ), $request->getDataSet()->getRows());
    }

    public function testDataSetNotFound() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('DataSet "message" was not found on remote response.');

        $filterCriteria = new FilterCriteria('message');
        $filterCriteria->addCondition('field', FilterCriteria::EQ, 'value');
        $request = new Request\Filter($filterCriteria, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));

        $response = new Response();
        $this->remoteServer->expects($this->once())
                           ->method('request')
                           ->with($request)
                           ->will($this->returnValue($response));

        $remoteDataSet = $this->dataSourceManager->findBy($request->getFilterCriteria());
    }

    public function testRemoteServerError() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error on remote server: "Invalid filter".');

        $filterCriteria = new FilterCriteria('message');
        $filterCriteria->addCondition('field', 'invalid', 'value');
        $request = new Request\Filter($filterCriteria, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));

        $response = new Response();
        $response->setError(new Error('Invalid filter', 0));
        $this->remoteServer->expects($this->once())
                           ->method('request')
                           ->with($request)
                           ->will($this->returnValue($response));

        $remoteDataSet = $this->dataSourceManager->findBy($request->getFilterCriteria());
    }

    public function testPopulateDataSet(){
        $dataSet = new DataSet('message', array(
            new Row(array('column' => array('__ALL' => [])))
        ));

        $filterCriteria = new FilterCriteria('message');

        $request = new Request\Filter($filterCriteria, self::HTTP_METHOD, self::ROUTE, self::USER_ID);

        $expectedMessages = array(
            new Row(array('__is_new' => false, '_id' => '0', 'author' => 'Author 1', 'message' => 'Foo', 'date' => '2016-10-14 10:20:35')),
            new Row(array('__is_new' => false, '_id' => '1', 'author' => 'Author 2', 'message' => 'Bar', 'date' => '2016-10-14 10:20:45')),
            new Row(array('__is_new' => false, '_id' => '2', 'author' => 'Author 1', 'message' => 'Baz', 'date' => '2016-10-14 10:20:55'))
        );
        $expectedDataSet = new DataSet('message', $expectedMessages);
        $response = new Response();
        $response->addDataSet($expectedDataSet);

        $this->requestProvider->expects($this->once())
                              ->method('getRequest')
                              ->will($this->returnValue($request));

        $this->remoteServer->expects($this->once())
                           ->method('request')
                           ->will($this->returnValue($response));

        $expectedResult = new DataSet('message', array(
            new Row(array(
                'column' => $expectedMessages
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