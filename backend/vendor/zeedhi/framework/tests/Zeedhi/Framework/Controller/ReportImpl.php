<?php
namespace tests\Zeedhi\Framework\Controller;

use Zeedhi\Framework\Controller\Report;

class ReportImpl extends Report{

    protected $reportMapping = array(
        "report_test" => array(
            "strategy" => "report_strategy_mock",
            "parameterMapping" => array(
                "field1" => "param1",
                "field2" => "param2",
            ),
        ),
        "report_with_invalid_strategy" => array(
            "strategy" => "invalid_strategy",
            "parameterMapping" => array(
                "field1" => "param1",
                "field2" => "param2",
            ),
        ),
    );

}