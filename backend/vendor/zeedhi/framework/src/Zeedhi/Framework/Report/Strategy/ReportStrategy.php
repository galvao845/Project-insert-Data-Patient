<?php
namespace Zeedhi\Framework\Report\Strategy;
use Zeedhi\Framework\Report\ReportFile;

/**
 * Interface ReportStrategy
 */
interface ReportStrategy {
	/**
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return string
	 */
	public function createRemoteReport($reportName, array $params);

	/**
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return ReportFile
	 */
	public function downloadReport($reportName, array $params);

	/**
	 * @return string
	 */
	public function getName();
}