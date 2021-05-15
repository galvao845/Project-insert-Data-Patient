<?php
namespace tests\Zeedhi\Framework\Report;

use Zeedhi\Framework\Report\Exception;

class ExceptionTest extends \PHPUnit\Framework\TestCase {

    public function testEmptyReport() {
        $exception = Exception::emptyReport();
        $this->assertInstanceOf('\Zeedhi\Framework\Report\Exception', $exception);
        $this->assertEquals("No data to be listed", $exception->getMessage());
    }

    public function testReportNotFound() {
        $exception = Exception::reportNotFound("Report_Name");
        $this->assertInstanceOf('\Zeedhi\Framework\Report\Exception', $exception);
        $this->assertEquals("No report found named 'Report_Name'.", $exception->getMessage());
    }

    public function testStrategyNotFound() {
        $exception = Exception::strategyNotFound("Strategy_Name");
        $this->assertInstanceOf('\Zeedhi\Framework\Report\Exception', $exception);
        $this->assertEquals("No strategy found named 'Strategy_Name'.", $exception->getMessage());
    }
}
