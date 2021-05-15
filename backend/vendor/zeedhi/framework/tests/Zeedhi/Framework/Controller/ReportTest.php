<?php
namespace tests\Zeedhi\Framework\Controller;

use tests\Zeedhi\Framework\Report\Mocks\Strategy;
use Zeedhi\Framework\DTO\Request\Row;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Report\ReportService;

class ReportTest extends \PHPUnit\Framework\TestCase {

    CONST USER_ID = 'kqq5qys4m9rudi';

    /** @var ReportImpl */
    protected $reportController;
    /** @var Strategy */
    protected $reportStrategyMock;

    protected function setUp() {
        $this->reportStrategyMock = new Strategy();
        $this->reportController = new ReportImpl(new ReportService(array($this->reportStrategyMock)));
    }

    public function testCreateRemoteReport() {
        $request = new Row(
            new \Zeedhi\Framework\DTO\Row(array(
                '__report_name' => "report_test",
                "field1"        => "value1",
                "field2"        => "value2",
            )),
            "POST",
            "/test/openReport",
            self::USER_ID
        );
        $response = new Response();
        $this->reportController->createRemoteReport($request, $response);
        $this->assertCount(1, $this->reportStrategyMock->calls);
        $call = current($this->reportStrategyMock->calls);
        $reportCallId = $call['callId'];
        $this->assertEquals("report_test", $call['reportName']);
        $callParams = $call['params'];
        $this->assertCount(2, $callParams);
        $this->arrayHasKey("param1", $callParams);
        $this->assertEquals("value1", $callParams["param1"]);
        $this->arrayHasKey("param2", $callParams);
        $this->assertEquals("value2", $callParams["param2"]);
        $messages = $response->getMessages();
        $this->assertCount(1, $messages);
        /** @var Response\Message $message */
        $message = current($messages);
        $this->assertEquals($reportCallId, $message->getMessage());
    }

    public function testInvalidStrategy() {
        $request = new Row(
            new \Zeedhi\Framework\DTO\Row(array(
                '__report_name' => "report_with_invalid_strategy",
                "field1"        => "value1",
                "field2"        => "value2",
            )),
            "POST",
            "/test/openReport",
            self::USER_ID
        );
        $response = new Response();
        $this->reportController->createRemoteReport($request, $response);
        $this->assertCount(0, $response->getMessages());
        $this->assertCount(0, $this->reportStrategyMock->calls);
        $error = $response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $error);
        $this->assertEquals("No strategy found named 'invalid_strategy'.", $error->getMessage());
    }

    public function testInvalidReportName() {
        $request = new Row(
            new \Zeedhi\Framework\DTO\Row(array(
                '__report_name' => "invalid_report_name",
                "field1"        => "value1",
                "field2"        => "value2",
            )),
            "POST",
            "/test/openReport",
            self::USER_ID
        );
        $response = new Response();
        $this->reportController->createRemoteReport($request, $response);
        $this->assertCount(0, $response->getMessages());
        $this->assertCount(0, $this->reportStrategyMock->calls);
        $error = $response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $error);
        $this->assertEquals("Mapping not found for report 'invalid_report_name'.", $error->getMessage());
    }

    public function testRowWithoutReportName() {
        $request = new Row(
            new \Zeedhi\Framework\DTO\Row(array(
                "field1"        => "value1",
                "field2"        => "value2",
            )),
            "POST",
            "/test/openReport",
            self::USER_ID
        );
        $response = new Response();
        $this->reportController->createRemoteReport($request, $response);
        $this->assertCount(0, $response->getMessages());
        $this->assertCount(0, $this->reportStrategyMock->calls);
        $error = $response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $error);
        $this->assertEquals("Missing report name in row.", $error->getMessage());
    }

    public function testMissingParameterFieldInRow() {
        $request = new Row(
            new \Zeedhi\Framework\DTO\Row(array(
                '__report_name' => "report_test",
                //"field1"        => "value1", //the missing field
                "field2"        => "value2",
            )),
            "POST",
            "/test/openReport",
            self::USER_ID
        );
        $response = new Response();
        $this->reportController->createRemoteReport($request, $response);
        $this->assertCount(0, $response->getMessages());
        $this->assertCount(0, $this->reportStrategyMock->calls);
        $error = $response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $error);
        $this->assertEquals("Missing field 'field1', used for parameter 'param1', at given row.", $error->getMessage());
    }
}