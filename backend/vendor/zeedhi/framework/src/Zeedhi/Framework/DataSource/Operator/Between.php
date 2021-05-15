<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class Between extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::BETWEEN, $dataSourceConfig);
    }

    protected function buildExpression($columnName, $parameterName) {
        return $this->getExpressionBuilder()->between($columnName, $parameterName.'_INIT_VALUE', $parameterName.'_END_VALUE');
    }

    protected function addParam(array $condition, array &$params) {
        $params[$condition['paramName'].'_INIT_VALUE'] = $condition['value'][0];
        $params[$condition['paramName'].'_END_VALUE'] = $condition['value'][1];
    }
}