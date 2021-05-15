<?php
namespace tests\Zeedhi\Framework\Remote;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response\Error;
use Zeedhi\Framework\DTO\Response\Message;
use Zeedhi\Framework\DTO\Response\Method;
use Zeedhi\Framework\DTO\Response\Notification;
use Zeedhi\Framework\Remote\HttpInterface;
use Zeedhi\Framework\Remote\RequestFactory;
use Zeedhi\Framework\Remote\Server;

class ServerTest extends \PHPUnit\Framework\TestCase {

    const ROUTE_PATH = '/random/route/path';
    const USER_ID = 'randomUserId';

    /** @var Server */
    protected $server;
    /** @var HttpInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $httpRequestStrategy;
    /** @var array */
    protected $authData = array('Auth' => 'test');
    /** @var RequestFactory */
    protected $requestFactory;

    public function setUp() {
        $this->httpRequestStrategy = $this->getMockBuilder('\Zeedhi\Framework\Remote\HttpInterface')
                                          ->setMethods(array('request', 'setHeaders', 'setBaseUrl', 'setMethod'))
                                          ->getMock();
        $this->server = new Server($this->httpRequestStrategy, $this->authData);
        $this->httpRequestStrategy->expects($this->any())
            ->method('setHeaders')
            ->with(array('Auth' => 'test', 'userId' => self::USER_ID, 'Content-Type' => 'application/json'));
        $this->requestFactory = new RequestFactory();
        $this->requestFactory->setUserId(self::USER_ID);
    }

    public function testParameters() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array("requestType" => "Empty", "foo" => "bar", "baz" => "qux"))
            ->willReturn('{}');

        $request = $this->requestFactory->createEmptyRequest("POST", self::ROUTE_PATH);
        $request->setParameter('foo', 'bar');
        $request->setParameter('baz', 'qux');
        $response = $this->server->request($request);

        $this->assertEquals('S', $response->getStatus());
        $this->assertNull($response->getError());
        $this->assertEmpty($response->getDataSets());
        $this->assertEmpty($response->getMessages());
        $this->assertEmpty($response->getNotifications());
    }

    public function testEmptyRequest() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array("requestType" => "Empty"))
            ->willReturn('{}');

        $request = $this->requestFactory->createEmptyRequest("POST", self::ROUTE_PATH);
        $response = $this->server->request($request);

        $this->assertEquals('S', $response->getStatus());
        $this->assertNull($response->getError());
        $this->assertEmpty($response->getDataSets());
        $this->assertEmpty($response->getMessages());
        $this->assertEmpty($response->getNotifications());
    }

    public function testDataSetRequest() {
        $rows = array(array('id' => '1'), array('id' => '2'));
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array('dataset' => $rows, "requestType" => "DataSet"))
            ->willReturn('{"dataset": { "dataSourceName": [{"id": "one"}, {"id": "two"} ] }}');

        $request = $this->requestFactory->createDataSetRequest('POST', self::ROUTE_PATH, new DataSet('dataSourceName', $rows));
        $response = $this->server->request($request);

        $this->assertEquals('S', $response->getStatus());
        $expectedDataSet = array(new DataSet('dataSourceName', array(array('id' => 'one'),array('id' => 'two'))));
        $this->assertEquals($expectedDataSet, $response->getDataSets());
        $this->assertNull($response->getError());
        $this->assertEmpty($response->getMethods());
        $this->assertEmpty($response->getMessages());
    }

    public function testNotifications() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array('requestType' => 'DataSet', 'dataset' => array(array('id' => '1'), array('id' => '2'))))
            ->willReturn('{"notifications":[{"message":"2 rows successifully persisted","type":"success","variables":[]}]}');

        $dataSet = new DataSet('dataSourceName', array(array('id' => '1'), array('id' => '2')));
        $request = new Request\DataSet($dataSet, 'POST', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);

        $this->assertEquals('S', $response->getStatus());
        $expectedNotifications = array(new Notification('2 rows successifully persisted'));
        $this->assertEquals($expectedNotifications, $response->getNotifications());
        $this->assertNull($response->getError());
        $this->assertEmpty($response->getDataSets());
        $this->assertEmpty($response->getMethods());
        $this->assertEmpty($response->getMessages());
    }

    public function testFilterRequest() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array(
                'requestType' => 'FilterData',
                'filter' => array(array('name'=>'id','operator'=>'<=','value'=>'3')),
                'page' => null,
                'itemsPerPage' => 300
            ))
            ->willReturn('{"dataset": { "dataSourceName": [{"id": "1"}, {"id": "2"}, {"id": "3"} ]}}');

        $filterCriteria = new FilterCriteria('dataSourceName');
        $filterCriteria->addCondition('id', '<=', '3');
        $request = new Request\Filter($filterCriteria, 'POST', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);

        $this->assertEquals('S', $response->getStatus());

        $rows = array(
            array('id' => '1'),
            array('id' => '2'),
            array('id' => '3')
        );

        $expectedDataSet = array(new DataSet('dataSourceName', $rows));
        $this->assertEquals($expectedDataSet, $response->getDataSets());
        $this->assertNull($response->getError());
        $this->assertEmpty($response->getNotifications());
        $this->assertEmpty($response->getMethods());
        $this->assertEmpty($response->getMessages());
    }

    public function testMessages() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array(
                'requestType' => 'FilterData',
                'filter'=>array(array('name' => 'id', 'operator' => '<=', 'value' => '3')),
                'page' => null,
                'itemsPerPage' => 300
            ))
            ->willReturn('{"messages": [{"message": "Random message for test purposes", "type": "M", "variables": []}]}');

        $filterCriteria = new FilterCriteria('dataSourceName');
        $filterCriteria->addCondition('id', '<=', '3');
        $request = new Request\Filter($filterCriteria, 'POST', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);

        $this->assertEquals('S', $response->getStatus());
        $this->assertNull($response->getError());
        $this->assertEmpty($response->getDataSets());
        $this->assertEmpty($response->getNotifications());
        $this->assertEmpty($response->getMethods());

        $expectedMessages = array(new Message('Random message for test purposes'));
        $this->assertEquals($expectedMessages, $response->getMessages());
    }

    public function testRowRequestAndError() {
        $row = array('login' => 'login', 'password' => 'password');
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array('requestType' => 'Row', 'row' => $row))
            ->willReturn('{"error": "Incorrect login"}');

        $request = new Request\Row($row, 'POST', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);

        $this->assertEquals('E', $response->getStatus());
        $this->assertEquals(new Error('Incorrect login', 0), $response->getError());
        $this->assertEmpty($response->getDataSets());
        $this->assertEmpty($response->getNotifications());
        $this->assertEmpty($response->getMethods());
        $this->assertEmpty($response->getMessages());
    }

    public function testMethods() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array('requestType' => 'Empty', ))
            ->willReturn('{"method": [{"name": "randomMethod", "parameters": []}]}');

        $request = new Request('POST', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);

        $expectedMethod = array(new Method('randomMethod'));
        $this->assertEquals($expectedMethod, $response->getMethods());
    }

    public function testRequestMethod() {
        $this->httpRequestStrategy->expects($this->exactly(2))
                                  ->method('request')
                                  ->will($this->returnValue('{}'));

        $this->httpRequestStrategy->expects($this->exactly(2))
                                  ->method('setMethod')
                                  ->withConsecutive(array('POST'), array('GET'));

        $request = new Request('POST', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);

        $request = new Request('GET', self::ROUTE_PATH, self::USER_ID);
        $response = $this->server->request($request);
    }

    public function testBadResponse() {
        try {
            $exceptionCaught = false;

            $this->httpRequestStrategy->expects($this->once())
                                      ->method('request')
                                      ->will($this->returnValue('not a json'));

            $request = new Request('POST', self::ROUTE_PATH, self::USER_ID);
            $response = $this->server->request($request);
        } catch (\Zeedhi\Framework\Remote\ServerException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Error parsing response', $e->getMessage());
            $this->assertEquals('not a json', $e->getResponseBody());
        }

        $this->assertTrue($exceptionCaught, 'Expected exception \Zeedhi\Framework\Remote\ServerException was not thrown');
    }

    public function testServerShouldAddContentTypeHeaderIfItIsMissing() {
        $this->httpRequestStrategy->expects($this->once())
            ->method('request')
            ->with(self::ROUTE_PATH, array('requestType' => 'Empty'))
            ->will($this->returnValue('{}'));

        $expectedHeaders = array(
            'Auth' => 'test',
            'Content-Type' => 'application/json',
            'userId' => 'randomUserId'
        );
        $this->httpRequestStrategy->expects($this->once())
                                  ->method('setHeaders')
                                  ->with($expectedHeaders);

        $request = new Request('POST', self::ROUTE_PATH, self::USER_ID);
        $this->server->request($request);
    }

}