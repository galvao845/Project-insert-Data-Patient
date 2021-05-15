<?php
namespace tests\Zeedhi\Framework\Remote;

use Zeedhi\Framework\Remote\RequestFactory;
use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;

class RequestFactoryTest extends \PHPUnit\Framework\TestCase {

    const USER_ID = 'userId';

    /** @var RequestFactory */
    protected $requestFactory;

    public function setUp() {
        $this->requestFactory = new RequestFactory();
        $this->requestFactory->setUserId(self::USER_ID);
    }

    public function testCreateEmptyRequest() {
        $request = $this->requestFactory->createEmptyRequest('POST', '/user/find');

        $this->assertInstanceOf('Zeedhi\Framework\DTO\Request', $request, 'Method createEmptyRequest must return instance of DTO\Request');
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/user/find', $request->getRoutePath());
        $this->assertEquals(self::USER_ID, $request->getUserId());
    }

    public function testCreateDataSetRequest() {
        $dataSet = new DataSet('dataSetName', array(1, 2, 3));
        $request = $this->requestFactory->createDataSetRequest('POST', '/user/find', $dataSet);

        $this->assertInstanceOf('Zeedhi\Framework\DTO\Request\DataSet', $request, 'Method createEmptyRequest must return instance of DTO\Request');

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/user/find', $request->getRoutePath());
        $this->assertEquals($dataSet, $request->getDataSet());
        $this->assertEquals(self::USER_ID, $request->getUserId());
    }

    public function testCreateFilterRequest() {
        $filterCriteria = new FilterCriteria('dataSourceName', array(array('a', '=', '123')));
        $request = $this->requestFactory->createFilterRequest('POST', '/user/find', $filterCriteria);

        $this->assertInstanceOf('Zeedhi\Framework\DTO\Request\Filter', $request, 'Method createEmptyRequest must return instance of DTO\Request');
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/user/find', $request->getRoutePath());
        $this->assertEquals($filterCriteria, $request->getFilterCriteria());
        $this->assertEquals(self::USER_ID, $request->getUserId());
    }

    public function testCreateRowRequest() {
        $row = array(1, 2, 3, 4);
        $request = $this->requestFactory->createRowRequest('POST', '/user/find', $row);

        $this->assertInstanceOf('Zeedhi\Framework\DTO\Request\Row', $request, 'Method createEmptyRequest must return instance of DTO\Request');
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/user/find', $request->getRoutePath());
        $this->assertEquals($row, $request->getRow());
        $this->assertEquals(self::USER_ID, $request->getUserId());
    }
}