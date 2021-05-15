<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class NotBetween extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::NOT_BETWEEN, $dataSourceConfig);
    }

    private function notBetween($val, $x, $y) {
        return $val . ' NOT BETWEEN ' . $x . ' AND ' . $y;
    }

    protected function buildExpression($columnName, $parameterName) {
        // should be, but does not exist so we implemented it.
        // return $this->getExpressionBuilder()->notBetween($columnName, $parameterName . '_INIT_VALUE', $parameterName . '_END_VALUE');
        return $this->notBetween($columnName, $parameterName . '_INIT_VALUE', $parameterName . '_END_VALUE');
    }

    protected function addParam(array $condition, array &$params) {
        $params[$condition['paramName'].'_INIT_VALUE'] = $condition['value'][0];
        $params[$condition['paramName'].'_END_VALUE'] = $condition['value'][1];
    }
}