<?php
namespace Zeedhi\Framework\Controller;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\Report\ReportService;

/**
 * Class Report
 *
 * Base class for reports
 *
 * @package Zeedhi\Framework\Controller
 */
abstract class Report extends Simple {

    const REPORT_NAME_FIELD = '__report_name';

    /** @var ReportService */
    protected $reportService;
    /** @var array */
    protected $reportMapping;

    public function __construct(ReportService $reportService) {
        $this->reportService = $reportService;
    }

    /**
     * @param string $reportName
     * @return array
     * @throws Exception
     */
    protected function getReportMapping($reportName) {
        if (isset($this->reportMapping[$reportName])) {
            return $this->reportMapping[$reportName];
        }

        throw Exception::reportNotMapped($reportName);
    }

    protected function getReportName(DTO\Row $row) {
        if ($row->has(self::REPORT_NAME_FIELD)) {
            return $row->get(self::REPORT_NAME_FIELD);
        }

        throw Exception::missingReportNameField();
    }

    public function getReportStrategy($reportName) {
        $reportMapping = $this->getReportMapping($reportName);
        return $reportMapping['strategy'];
    }

    public function prepareParametersRow($reportName, DTO\Row $row) {
        $parametersRow = array();
        $reportMapping = $this->getReportMapping($reportName);
        foreach ($reportMapping['parameterMapping'] as $fieldName => $paramName) {
            if ($row->has($fieldName)) {
                $parametersRow[$paramName] = $row->get($fieldName);
            } else {
                throw Exception::missingFieldForParameter($fieldName, $paramName);
            }
        }

        return $parametersRow;
    }

    public function createRemoteReport(DTO\Request\Row $request, DTO\Response $response) {
        try {
            $this->beforeProcessReport($request, $response);
            $reportURL = $this->processReport($request);
            $this->afterProcessReport($request, $response, $reportURL);
            /** @TODO temporary solution */
            $response->addMessage(new DTO\Response\Message($reportURL));
            /** @TODO $response->open | download | show($report); */
        } catch (\Exception $e) {
            $response->setCriticalError(new DTO\Response\Error($e->getMessage(), $e->getCode(), $e->getTraceAsString()));
        }
    }

    public function beforeProcessReport(DTO\Request\Row $request, DTO\Response $response) {}
    public function afterProcessReport(DTO\Request\Row $request, DTO\Response $response, $report) {}

    /**
     * @param DTO\Request\Row $request
     * @return mixed
     * @throws Exception
     */
    protected function processReport(DTO\Request\Row $request) {
        $row = $request->getRow();
        $reportName = $this->getReportName($row);
        $reportStrategy = $this->getReportStrategy($reportName);
        $parametersRow = $this->prepareParametersRow($reportName, $row);
        return $this->reportService->createRemoteReport($reportStrategy, $reportName, $parametersRow);
    }
}