<?php
namespace tests\Zeedhi\Framework\HTTP;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DTO\Request\Row;
use Zeedhi\Framework\HTTP\Kernel;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Security\Support\Cors;
use Zeedhi\Framework\Security\Support\CorsOptions;

class KernelTest extends \PHPUnit\Framework\TestCase {

    protected static $URL = "/framework/backend/service/index.php";
    /** @var Kernel */
    protected $kernel;
    /** @var Cors */
    protected $corsService;

    public function setUp() {
        $corsOptions = new CorsOptions('*', '*', '*', 0, false, false);
        $this->corsService = new Cors($corsOptions);
        $this->kernel = new Kernel(self::$URL, $this->corsService, null, false);
    }

    public function assertKernelInstance() {
        $this->assertInstanceOf('Zeedhi\Framework\HTTP\Kernel', $this->kernel, 'Its is expected an instance of Kernel');
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Request', $this->kernel->getRequest(), 'Its is expected an instance of DTO\Request');
        $this->assertInstanceOf('Zeedhi\Framework\HTTP\Request', $this->kernel->getHttpRequest(), 'Its is expected an instance of HTTP\Request');
    }

    public function testKernelGetRequestWithDataSet() {
        $this->createFakeRequestByType('dataset');
        $this->assertKernelInstance();
        $this->assertRequestByClass('Zeedhi\Framework\DTO\Request\DataSet');
        /** @var \Zeedhi\Framework\DTO\Request\DataSet $request */
        $request = $this->kernel->getRequest();
        $rows = $request->getDataSet()->getRows();
        $this->assertContainsOnlyInstancesOf('Zeedhi\Framework\DTO\Row', $rows);
    }

    public function testKernelGetRequestFilterData() {
        $this->createFakeRequestByType('filter');
        $this->assertKernelInstance();
        $this->assertRequestByClass('Zeedhi\Framework\DTO\Request\Filter');
        $request = $this->kernel->getRequest();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Request\Filter', $request);
    }

    public function testKernelGetRequestFilterDataWithOrderAndGroup() {
        $this->createFakeRequestByType('filter');
        $this->assertKernelInstance();
        $this->assertRequestByClass('Zeedhi\Framework\DTO\Request\Filter');
        $request = $this->kernel->getRequest();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Request\Filter', $request);

        /** @var $request \Zeedhi\Framework\DTO\Request\Filter */
        $filterCriteria = $request->getFilterCriteria();

        $orderBy = $filterCriteria->getOrderBy();
        $this->assertTrue(is_array($orderBy), "Order by must be a array.");
        $this->assertArrayHasKey('REGION_NAME', $orderBy, "Order by must be indexed by column names");
        $this->assertEquals(FilterCriteria::ORDER_ASC, $orderBy['REGION_NAME'], "Order by values must be ASC or DESC.");

        $groupBy = $filterCriteria->getGroupBy();
        $this->assertTrue(is_array($groupBy), "Group by must be a array.");
        $this->assertContains('REGION_NAME', $groupBy, "Group by have a column 'REGION_NAME'");
    }

    public function testKernelGetRequestWithRow() {
        $this->createFakeRequestByType('row');
        $this->assertKernelInstance();
        $this->assertRequestByClass('Zeedhi\Framework\DTO\Request\Row');
        /** @var Row $request */
        $request = $this->kernel->getRequest();
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Row', $request->getRow());
    }

    public function testKernelGetRequestWithParameters() {
        $this->createFakeRequestByType('empty');
        $_POST['foo'] = 'bar';
        $_POST['baz'] = 'qux';

        $this->assertKernelInstance();
        $request = $this->kernel->getRequest();
        $expectedParameters = array(
            'foo' => 'bar',
            'baz' => 'qux'
        );
        $this->assertEquals($expectedParameters, $request->getParameters());
    }

    public function testKernelSendResponseSuccess() {
        $this->assertKernelInstance();
        $this->assertResponse($this->sendResponse($this->kernel, Response::STATUS_SUCCESS));
    }

    public function testKernelSendResponseError() {
        $this->assertKernelInstance();
        $this->assertResponse($this->sendResponse($this->kernel, Response::STATUS_ERROR));
    }

    private function createFakeRequestByType($type) {
        $_GET = array();
        $_POST = array();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/framework/backend/service/index.php/router';
        switch ($type) {
            case 'row':
                $_POST[$type]['USERNAME'] = 'admin';
                $_POST[$type]['PASSWORD'] = '123';
                $_POST['requestType'] = 'Row';
                break;
            case 'dataset':
                $_POST[$type][0]['ZIP_CODE'] = '1231313131';
                $_POST['requestType'] = 'DataSet';
                break;
            case 'filter':
                $_POST[$type][0]['operator'] = '=';
                $_POST[$type][0]['value'] = '';
                $_POST[$type][0]['name'] = 'ENTERPRISE';
                $_POST[$type][1]['name'] = array(array('name' => 'REGION_NAME', 'direction' => 'ASC'));
                $_POST[$type][1]['operator'] = 'ORDER_BY';
                $_POST[$type][2]['name'] = array('REGION_NAME');
                $_POST[$type][2]['operator'] = 'GROUP_BY';
                $_POST['page'] = 0;
                $_POST['requestType'] = 'FilterData';
                $_POST['page'] = 1;
                $_POST['itemsPerPage'] = 100;
                break;
            default:
                break;
        }
    }

    private function assertRequestByClass($class) {
        $request = $this->kernel->getRequest();
        $this->assertEquals('/router', $request->getRoutePath(), 'It is expected routePath as /router');
        $this->assertEquals('POST', $request->getMethod(), 'Its is expected POST method');
        $this->assertInstanceOf($class, $request, 'Its is expected an instance of ' . $class);
    }

    private function sendResponse(Kernel $kernel, $responseStatus) {
        $response = new Response($responseStatus);
        $response->addDataSet(new DataSet('REGIONS', array()));
        $response->addNotification(new Response\Notification(" This is blasphemy! This is madness!"));
        $response->addMessage(new Response\Message('Madness? THIS IS SPARTA!'));
        $response->setError(new Response\Error('Proxy!!', 403));
        $response->addMethod(new Response\Method('openWindow', array('')));
        ob_start();
        $kernel->sendResponse($response);
        $content = ob_get_contents();
        ob_end_clean();
        return json_decode($content);
    }

    private function assertResponse($content) {
        $this->assertObjectHasAttribute('dataset', $content, 'Return must be constain an object has attribute dataset');
        $this->assertObjectHasAttribute('method', $content, 'Return must be constain an object has attribute method');
        $this->assertObjectHasAttribute('messages', $content, 'Return must be constain an an object has attribute messages');
        $this->assertObjectHasAttribute('notifications', $content, 'Return must be constain an an object has attribute messages');
    }

    public function testCleanupURI() {
        $refCleanup = new \ReflectionMethod($this->kernel, 'cleanupURI');
        $refCleanup->setAccessible(true);
        $routeUri = $refCleanup->invoke($this->kernel, '/v1'.self::$URL.'/user');
        $this->assertEquals($routeUri, '/user');
    }

    public function testInvalidResponseContent() {
        $this->createFakeRequestByType('filter');
        $request = $this->kernel->getRequest();
        $expectedResponse = '{"error":"Cant encode response content: Malformed UTF-8 characters, possibly incorrectly encoded"}';
        $response = new Response(Response::STATUS_SUCCESS);
        $response->addMessage(new Response\Message(file_get_contents(__DIR__.'/Response/non_unicode_content.txt')));
        ob_start();
        $this->kernel->sendResponse($response);
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($expectedResponse, $content);
    }

    public function testErrorResponseContent(){
        $this->createFakeRequestByType('row');
        $request = $this->kernel->getRequest();
        //Stacktrace should not appear when isDevMode == false.
        $expectedResponse = '{"error":"Upgrade Required","errorCode":426}';
        $response = new Response();
        $response->setError(new Response\Error('Upgrade Required', 426, 'random stackTrace'));
        ob_start();
        $this->kernel->sendResponse($response);
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($expectedResponse, $content);
    }

    public function testIsDevModeTrueReturningMoreInfo(){
        $this->kernel = new Kernel(self::$URL, $this->corsService, true);
        $this->createFakeRequestByType('row');
        $request = $this->kernel->getRequest();
        $response = new Response();
        $exception1 = new \Exception("Required", 426);
        $exception2 = new \Exception("Damn", 407, $exception1);
        $response->setError(new Response\Error("Internal server error", 500, "traced", $exception2));
        ob_start();
        $this->kernel->sendResponse($response);
        $content = ob_get_contents();
        $content = json_decode($content, true);
        ob_end_clean();
        $this->assertEquals("Internal server error", $content['error']);
        $this->assertEquals(500, $content['errorCode']);
        $this->assertEquals("traced", $content['stackTrace']);
        $this->assertEquals($exception2->getMessage(), $content['exceptions'][0]['message']);
        $this->assertEquals($exception2->getCode(), $content['exceptions'][0]['code']);
        $this->assertEquals($exception2->getTraceAsString(), $content['exceptions'][0]['stackTrace']);
        $this->assertEquals($exception1->getMessage(), $content['exceptions'][1]['message']);
        $this->assertEquals($exception1->getCode(), $content['exceptions'][1]['code']);
        $this->assertEquals($exception1->getTraceAsString(), $content['exceptions'][1]['stackTrace']);
    }

    public function testKernelFilterConditionWithoutValue() {
        $this->createFakeRequestByType('filter');

        $_POST['filter'] = array(
            array(
                'name' 	   => 'COLUMN_NAME',
                'operator' => FilterCriteria::IS_NOT_NULL
            )
        );

        $this->assertKernelInstance();
        $this->assertRequestByClass('Zeedhi\Framework\DTO\Request\Filter');
        $request = $this->kernel->getRequest();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Request\Filter', $request);

        /** @var $request \Zeedhi\Framework\DTO\Request\Filter */
        $filterCriteria = $request->getFilterCriteria();
        $conditions = $filterCriteria->getConditions();
        $this->assertTrue(is_array($conditions));
        $this->assertCount(1, $conditions);
        $this->assertEquals($conditions[0], array(
            'columnName' => 'COLUMN_NAME',
            'operator'   => FilterCriteria::IS_NOT_NULL,
            'value'      => null
        ));
    }
}