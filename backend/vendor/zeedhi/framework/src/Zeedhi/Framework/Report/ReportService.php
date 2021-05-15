<?php
namespace Zeedhi\Framework\Report;

/**
 * Class ReportService
 */
class ReportService {

	/** @var Strategy\ReportStrategy[] */
	protected $reportStrategies;

	public function __construct(array $reportStrategies) {
		$this->reportStrategies = $reportStrategies;
	}

    /**
     * @param $strategyName
     * @return Strategy\ReportStrategy
     * @throws Exception
     */
	protected function getStrategyByName($strategyName) {
		foreach ($this->reportStrategies as $reportStrategy) {
			if ($reportStrategy->getName() == $strategyName) {
				return $reportStrategy;
			}
		}
		throw Exception::strategyNotFound($strategyName);
	}

	/**
	 * @param       $strategyName
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function createRemoteReport($strategyName, $reportName, array $params = array()) {
		$reportStrategy = $this->getStrategyByName($strategyName);
		return $reportStrategy->createRemoteReport($reportName, $params);
	}

	/**
	 * @param       $strategyName
	 * @param       $reportName
	 * @param array $params
	 *
	 * @return ReportFile
	 * @throws Exception
	 */
	public function downloadReport($strategyName, $reportName, array $params = array()) {
		$reportStrategy = $this->getStrategyByName($strategyName);
		return $reportStrategy->downloadReport($reportName, $params);
	}

}