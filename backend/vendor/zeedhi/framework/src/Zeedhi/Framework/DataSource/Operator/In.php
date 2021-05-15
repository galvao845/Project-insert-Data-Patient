<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class In extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::IN, $dataSourceConfig);
    }

    protected function buildExpression($columnName, $parameterName) {
        return $this->getExpressionBuilder()->in($columnName, $parameterName);
    }
}