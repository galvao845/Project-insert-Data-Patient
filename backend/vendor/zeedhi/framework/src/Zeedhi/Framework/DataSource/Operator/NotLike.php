<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class NotLike extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::NOT_LIKE, $dataSourceConfig);
    }

    protected function buildExpression($columnName, $parameterName) {
        return $this->getExpressionBuilder()->notLike($columnName, $parameterName);
    }
}