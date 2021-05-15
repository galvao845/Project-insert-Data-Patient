<?php
namespace Zeedhi\Framework\Report\Strategy;
use Zeedhi\Framework\Report\ReportFile;

/**
 * Class BirtStrategy
 */
class BirtStrategy implements ReportStrategy {

    const OVERWRITE_FORMAT_PARAM = '__format';
	const DYNAMIC_REL = 'D';
	const STATIC_REL = 'E';
	const FORMAT_HTML = 'html';
	const FORMAT_PDF = 'pdf';
	const VERSION = '5.07.006 WEB';
	const VIEW_MODE = 'frameset';
	const DEFAULT_LOCALE = 'pt_BR';
	const DEFAULT_TIMEZONE = 'America/Sao_Paulo';
	const BIRT = 'BIRT';

	protected $logoPath;
	protected $reportPath;
	protected $confPath;
	protected $reportType;
	protected $reportServerUrl;
	protected $reportFormat;
	protected $reportLocale;
	protected $reportTimezone;
	protected $viewMode;

    public function __construct($logoPath, $reportPath,
								$confPath, $reportServerUrl,
								$reportType = self::STATIC_REL,
								$reportFormat = self::FORMAT_HTML,
								$viewMode = self::VIEW_MODE,
								$reportLocale = self::DEFAULT_LOCALE,
								$reportTimezone = self::DEFAULT_TIMEZONE) {
		$this->logoPath = $logoPath;
		$this->reportPath = $reportPath;
		$this->confPath = $confPath;
		$this->reportType = $reportType;
		$this->reportServerUrl = $reportServerUrl;
		$this->reportFormat = $reportFormat;
		$this->viewMode = $viewMode;
		$this->reportLocale = $reportLocale;
		$this->reportTimezone = $reportTimezone;
	}

	/**
	 * @return mixed
	 */
	public function getLogoPath() {
		return $this->logoPath;
	}

	/**
	 * @param mixed $logoPath
	 */
	public function setLogoPath($logoPath) {
		$this->logoPath = $logoPath;
	}

	/**
	 * @return mixed
	 */
	public function getReportPath() {
		return $this->reportPath;
	}

	/**
	 * @param mixed $reportPath
	 */
	public function setReportPath($reportPath) {
		$this->reportPath = $reportPath;
	}

	/**
	 * @return mixed
	 */
	public function getConfPath() {
		return $this->confPath;
	}

	/**
	 * @param mixed $confPath
	 */
	public function setConfPath($confPath) {
		$this->confPath = $confPath;
	}

	/**
	 * @return string
	 */
	public function getReportType() {
		return $this->reportType;
	}

	/**
	 * @param string $reportType
	 */
	public function setReportType($reportType) {
		$this->reportType = $reportType;
	}

	/**
	 * @return mixed
	 */
	public function getReportServerUrl() {
		return $this->reportServerUrl;
	}

	/**
	 * @param mixed $reportServerUrl
	 */
	public function setReportServerUrl($reportServerUrl) {
		$this->reportServerUrl = $reportServerUrl;
	}

	/**
	 * @return string
	 */
	public function getReportFormat() {
		return $this->reportFormat;
	}

	/**
	 * @param string $reportFormat
	 */
	public function setReportFormat($reportFormat) {
		$this->reportFormat = $reportFormat;
	}

	/**
	 * @return string
	 */
	public function getViewMode() {
		return $this->viewMode;
	}

	/**
	 * @param string $viewMode
	 */
	public function setViewMode($viewMode) {
		$this->viewMode = $viewMode;
	}

	/**
	 * @return string
	 */
	public function getReportLocale() {
		return $this->reportLocale;
	}

	/**
	 * @param string $reportLocale
	 */
	public function setReportLocale($reportLocale) {
		$this->reportLocale = $reportLocale;
	}

	/**
	 * @return string
	 */
	public function getReportTimezone() {
		return $this->reportTimezone;
	}

	/**
	 * @param string $reportTimezone
	 */
	public function setReportTimezone($reportTimezone) {
		$this->reportTimezone = $reportTimezone;
	}

	protected function makeReportFileByURL($reportFileUrl) {
		$content = file_get_contents($reportFileUrl);
		$reportFile = new ReportFile(null, false);
		$reportFile->setContent($content);
		return $reportFile;
	}

	protected function formatBirtParamsFromArray($params) {
		$reportFormat = $this->reportFormat;
		$reportLocale = $this->reportLocale;
		$reportTimezone = $this->reportTimezone;
		$preparedParams = array();
		foreach ($params as $paramName => $value) {
			if ($paramName === self::OVERWRITE_FORMAT_PARAM) {
				$reportFormat = $value;
			} else {
				$preparedParams[] = "$paramName=$value";
			}
		}
		$strParams = "&__format={$reportFormat}&__svg=false&__locale=".urlencode($reportLocale)."&__timezone=".urlencode($reportTimezone);
		$strParams .= "&P_VERSAO=" . urlencode(self::VERSION);
		$strParams .= '&' . implode('&', $preparedParams);
		$strParams .= "&IMG_SRC=" . urlencode($this->getLogoPath());
		return $strParams;
	}

	protected function mountBirtReportUrl($reportName, $params) {
		$reportFilePath = $this->getReportPath() . '/' . $reportName . '.rptdesign';
		$reportUrl = $this->reportServerUrl . "/birt-viewer/{$this->viewMode}?__report=";
		$reportUrl .= urlencode($reportFilePath);
		$reportUrl .= $params;
		$reportUrl .= "&PATH=" . $this->getConfPath();
		return $reportUrl;
	}

	/**
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return string
	 */
	public function createRemoteReport($reportName, array $params) {
		$params = $this->formatBirtParamsFromArray($params);
		$reportUrl = $this->mountBirtReportUrl($reportName, $params);
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
		return self::BIRT;
	}
}