<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class IsNull extends DefaultOperator{

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::IS_NULL, $dataSourceConfig);
    }

    protected function buildExpression($columnName, $parameterName) {
        return $this->getExpressionBuilder()->isNull($columnName);
    }

    protected function addParam(array $condition, array &$params) {
        // Is Null has no params.
    }
}