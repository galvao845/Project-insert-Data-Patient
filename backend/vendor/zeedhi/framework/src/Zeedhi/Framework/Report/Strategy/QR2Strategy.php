<?php
namespace Zeedhi\Framework\Report\Strategy;

use Zeedhi\Framework\Report\Exception;
use Zeedhi\Framework\Report\ReportFile;

/**
 * Class QR2Strategy
 */
class QR2Strategy implements ReportStrategy {

	const EMPTY_REL = 'AVISO_REL_VAZIO';
	const ARQ_N_EXISTE = 'ERRO_ARQ_NAO_EXISTE';
	const QR2 = 'QR2';

	protected $qr2Type;
	protected $baseUrl;
	protected $serverUrl;
	protected $port;
	protected $language;
	protected $options;

	public function __construct($qr2Type, $baseUrl, $serverUrl, $port, $language = 'POR', $options = array()) {
		$this->qr2Type = $qr2Type;
		$this->baseUrl = $baseUrl;
		$this->serverUrl = $serverUrl;
		$this->port = $port;
		$this->language = $language;
		$this->options = $options;
	}

	/**
	 * @return mixed
	 */
	public function getQr2Type() {
		return $this->qr2Type;
	}

	/**
	 * @param mixed $qr2Type
	 */
	public function setQr2Type($qr2Type) {
		$this->qr2Type = $qr2Type;
	}

	/**
	 * @return mixed
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	/**
	 * @param mixed $baseUrl
	 */
	public function setBaseUrl($baseUrl) {
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @return mixed
	 */
	public function getServerUrl() {
		return $this->serverUrl;
	}

	/**
	 * @param mixed $serverUrl
	 */
	public function setServerUrl($serverUrl) {
		$this->serverUrl = $serverUrl;
	}

	/**
	 * @return mixed
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param mixed $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param string $language
	 */
	public function setLanguage($language) {
		$this->language = $language;
	}

	protected function createSoapClient($url, $options) {
		return new \SoapClient($url, $options);
	}

	protected function formatQR2Params($reportName, $params) {
		$preparedParams = "";
		foreach ($params as $name => $value) {
			$preparedParams .= $name . "=" . $value . ";";
		}
		$preparedParams = trim($preparedParams, ";");

		return array(
			'Relatorio' => $reportName,
			'Parametros' => $preparedParams,
			'Tipo' => $this->getQR2Type(),
			'Porta' => $this->getPort(),
			'Lang' => $this->getLanguage()
		);

	}

	protected function mountWSUrl() {
		return $this->baseUrl . $this->serverUrl;
	}

	protected function makeReportFileByURL($reportFileUrl) {
		$content = file_get_contents($reportFileUrl);
		$reportFile = new ReportFile(null, false);
		$reportFile->setContent($content);
		return $reportFile;
	}

	protected function getReportOnWS($reportName, $params) {
		$params = $this->formatQR2Params($reportName, $params);
		$soapClient = $this->createSoapClient($this->mountWSUrl(), $this->options);

		$remoteFileName = (string)$soapClient->getReport($params)->GetReportResult;
		if ($remoteFileName === self::ARQ_N_EXISTE) {
			throw Exception::reportNotFound($reportName);
		} elseif ($remoteFileName === self::EMPTY_REL) {
			throw Exception::emptyReport();
		}
		return $this->getReportFileUrl($remoteFileName);
	}

	private function getReportFileUrl($remoteFileName) {
		return $this->baseUrl . 'pdfreports/' . $remoteFileName;
	}

	/**
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function createRemoteReport($reportName, array $params) {
		return $this->getReportOnWS($reportName, $params);
	}

	public function downloadReport($reportName, array $params) {
		$reportFileUrl = $this->createRemoteReport($reportName, $params);
		return $this->makeReportFileByURL($reportFileUrl);
	}

	/**
	 * @return string
	 */
	final public function getName() {
		return self::QR2;
	}
}