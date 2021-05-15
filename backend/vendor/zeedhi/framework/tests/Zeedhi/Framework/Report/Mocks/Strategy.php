<?php
namespace tests\Zeedhi\Framework\Report\Mocks;

use Zeedhi\Framework\Report\ReportFile;
use Zeedhi\Framework\Report\Strategy\ReportStrategy;

class Strategy implements ReportStrategy{

    public $calls = array();

    /**
     * @param string $reportName
     * @param array  $params
     *
     * @return string
     */
    public function createRemoteReport($reportName, array $params) {
        $reportCallId = uniqid("report_call_");
        $this->calls[] = array(
            "reportName" => $reportName,
            "params"     => $params,
            "callId"     => $reportCallId,
        );

        return $reportCallId;
    }

    /**
     * @param string $reportName
     * @param array  $params
     *
     * @return ReportFile
     */
    public function downloadReport($reportName, array $params) {
        $callId = $this->createRemoteReport($reportName, $params);
        $reportFile = new ReportFile("../reports/".$callId, false);
        $reportFile->setContent(var_export($params, true));
        return $reportFile;
    }

    /**
     * @return string
     */
    public function getName() {
        return "report_strategy_mock";
    }
}