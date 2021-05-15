<?php

namespace Zeedhi\Framework\Report\Strategy;
use Zeedhi\Framework\Report\ReportFile;

/**
 * Class ZeedhiStrategy
 */
class ZeedhiStrategy implements ReportStrategy {

	const ZEEDHI = 'ZEEDHI';

	protected $reportServerUrl;

	public function __construct($reportServerUrl) {
		$this->reportServerUrl = $reportServerUrl;
	}


	protected function makeReportFileByURL($reportFileUrl) {
		$cURLHandler = curl_init($reportFileUrl);
		curl_setopt($cURLHandler, CURLOPT_HEADER, false);
		curl_setopt($cURLHandler, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cURLHandler);
		curl_close($cURLHandler);
		$reportFile = new ReportFile(null, false);
		$reportFile->setContent($content);
		return $reportFile;
	}

	protected function mountZeedhiReportUrl($reportName) {
		return $this->reportServerUrl . "/{$reportName}/";
	}

	/**
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return string
	 */
	public function createRemoteReport($reportName, array $params) {
		$reportUrl = $this->mountZeedhiReportUrl($reportName);
		return $reportUrl;
	}

	/**
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return ReportFile
	 */
	public function downloadReport($reportName, array $params) {
		$reportFileUrl = $this->createRemoteReport($reportName, $params);
		return $this->makeReportFileByURL($reportFileUrl);
	}


	/**
	 * @return string
	 */
	final public function getName() {
		return self::ZEEDHI;
	}
}