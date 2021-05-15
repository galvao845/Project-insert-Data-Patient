<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class LikeI extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::LIKE_I, $dataSourceConfig);
    }

    protected function buildExpression($columnName, $parameterName) {
        $expressionBuilder = $this->getExpressionBuilder();

        $columnName = $expressionBuilder->lower($columnName);
        $parameterName = $expressionBuilder->lower($parameterName);

        return $this->getExpressionBuilder()->like($columnName, $parameterName);
    }
}