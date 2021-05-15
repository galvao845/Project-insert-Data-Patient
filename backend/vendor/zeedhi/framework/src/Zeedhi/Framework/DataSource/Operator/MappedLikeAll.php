<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Doctrine\DBAL\Query\QueryBuilder;
use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\FilterCriteria;

class MappedLikeAll extends DefaultOperator {

    public function __construct(Configuration $dataSourceConfig) {
        parent::__construct(FilterCriteria::MAPPED_LIKE_ALL, $dataSourceConfig);
    }

    protected function factoryParamName(array $condition) {
        foreach($condition['value'][0] as $key => $value){
            $paramsName[$key] = uniqid('MAPPED_LIKE_ALL_');
        }
        return $paramsName;
    }

    /**
     * @param array $condition
     * @param array $params
     */
    protected function addParam(array $condition, array &$params) {
        $paramsName = $condition['paramName'];
        foreach($paramsName as $key => $paramName){
            $params[$paramName] = $condition['value'][0][$key];
        }
    }

    protected function getColumnsNames($conditionColumnName){
        $columnsList = explode('#', $conditionColumnName);
        $columns = array();
        $columns['_ALL'] = $columnsList[0] === '*' ? $this->dataSourceConfig->getColumnsForResultSet() : explode('|', $columnsList[0]);
        $columns['_ALL_MAP'] = isset($columnsList[1]) ? explode('&', $columnsList[1]) : array();
        return $columns;
    }

    protected function buildExpressionFromCondition($condition) {
        $paramName = $condition['paramName'];
        $columnName = $condition['columnName'];
        $expressionBuilder = $this->getExpressionBuilder();
        $expressions = array();
        $columnsList = $this->getColumnsNames($columnName);

        foreach ($columnsList['_ALL_MAP'] as $columnInList) {
            $expressions[] = $expressionBuilder->in($columnInList, ':'.$paramName[$columnInList]);
        }

        $bindColumnName = $expressionBuilder->lower(':'.$paramName['_ALL']);
        foreach ($columnsList['_ALL'] as $columnInList) {
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
            $columns = $this->getColumnsNames($dataColumnName);
            $columns['_ALL'] = implode('|', array_map($convertDataColumnIntoColumn, $columns['_ALL']));
            $columns['_ALL_MAP'] = implode('&', array_map($convertDataColumnIntoColumn, $columns['_ALL_MAP']));
            $columnName = $columns['_ALL_MAP'] !== "" ? $columns['_ALL'].'#'.$columns['_ALL_MAP'] : $columns['_ALL'];
        }

        return $columnName;
    }
}