<?php
namespace tests\Zeedhi\Framework\Report;

use tests\Zeedhi\Framework\Report\Mocks\Strategy;
use Zeedhi\Framework\Report\ReportService;

class ReportServiceTest extends \PHPUnit\Framework\TestCase {
    /** @var ReportService */
    protected $reportService;
    /** @var Strategy */
    protected $reportStrategyMock;

    protected function setUp() {
        $this->reportStrategyMock = new Strategy();
        $this->reportService = new ReportService(array($this->reportStrategyMock));
    }

    public function testDownload() {
        $params = array(
            "param1" => "value1",
            "param2" => "value2",
        );
        $reportField = $this->reportService->downloadReport("report_strategy_mock", "report_test", $params);
        $this->assertEquals(var_export($params, true), $reportField->getContent());
        $this->assertCount(1, $this->reportStrategyMock->calls);
        $call = current($this->reportStrategyMock->calls);
        $this->assertEquals("report_test", $call['reportName']);
        $callParams = $call['params'];
        $this->assertCount(2, $callParams);
        $this->arrayHasKey("param1", $callParams);
        $this->assertEquals("value1", $callParams["param1"]);
        $this->arrayHasKey("param2", $callParams);
        $this->assertEquals("value2", $callParams["param2"]);
    }
}
