<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class Like extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::LIKE, $dataSourceConfig);
    }

    protected function buildExpression($columnName, $parameterName) {
        return $this->getExpressionBuilder()->like($columnName, $parameterName);
    }
}