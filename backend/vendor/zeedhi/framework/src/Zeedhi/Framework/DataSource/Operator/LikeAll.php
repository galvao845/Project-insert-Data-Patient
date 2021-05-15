<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Doctrine\DBAL\Query\QueryBuilder;
use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class LikeAll extends DefaultOperator {

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::LIKE_ALL, $dataSourceConfig);
    }

    protected function factoryParamName(array $condition) {
        return uniqid('LIKE_ALL_');
    }

    protected function buildExpressionFromCondition($condition) {
        $columnName = $condition['columnName'];
        $paramName = $condition['paramName'];
        $columnList = $columnName === '*' ? $this->dataSourceConfig->getColumnsForResultSet() : explode('|', $columnName);
        $expressionBuilder = $this->getExpressionBuilder();
        $bindColumnName = $expressionBuilder->lower(':'.$paramName);
        $expressions = array();
        foreach ($columnList as $columnInList) {
            $columnInList = $expressionBuilder->lower($columnInList);
            $expressions[] = $expressionBuilder->like($columnInList, $bindColumnName);
        }

        return $expressionBuilder->orX()->addMultiple($expressions);
    }

    public function convertDataColumnForColumn($dataColumnName) {
        if ($dataColumnName === '*') {
            $columnName = '*';
        } else {
            $convertDataColumnIntoColumn = function ($dataColumn) {
                return parent::convertDataColumnForColumn($dataColumn);
            };
            $columnName = implode('|', array_map($convertDataColumnIntoColumn, explode('|', $dataColumnName)));
        }

        return $columnName;
    }
}