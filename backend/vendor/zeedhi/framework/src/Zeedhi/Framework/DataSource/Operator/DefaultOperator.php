<?php
namespace Zeedhi\Framework\DataSource\Operator;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\Exception;
use Zeedhi\Framework\DataSource\FilterCriteria;

class DefaultOperator {

    protected $stringRepresentation;
    protected $dataSourceConfig;

    public static $operatorMapping = array(
        FilterCriteria::LIKE_ALL        => LikeAll::class,
        FilterCriteria::MAPPED_LIKE_ALL => MappedLikeAll::class,
        FilterCriteria::LIKE            => Like::class,
        FilterCriteria::LIKE_I          => LikeI::class,
        FilterCriteria::IN              => In::class,
        FilterCriteria::NOT_IN          => NotIn::class,
        FilterCriteria::NOT_LIKE        => NotLike::class,
        FilterCriteria::IS_NULL         => IsNull::class,
        FilterCriteria::IS_NOT_NULL     => IsNotNull::class,
        FilterCriteria::BETWEEN         => Between::class,
        FilterCriteria::NOT_BETWEEN     => NotBetween::class
    );

    public function __construct($stringRepresentation, Configuration $dataSourceConfig) {
        $this->stringRepresentation = $stringRepresentation;
        $this->dataSourceConfig = $dataSourceConfig;
    }

    protected function getExpressionBuilder() {
        return new Expr();
    }

    /**
     * @param string $columnName
     * @param string $parameterName
     *
     * @return Expr\Comparison
     */
    protected function buildExpression($columnName, $parameterName) {
        return new Expr\Comparison($columnName, $this->stringRepresentation, $parameterName);
    }

    protected function buildExpressionFromCondition($condition) {
        $columnName = $condition['columnName'];
        return $this->buildExpression($columnName, ':'.$condition['paramName']);
    }

    /**
     * @param array $condition
     * @param array $params
     */
    protected function addParam(array $condition, array &$params) {
        $paramName = $condition['paramName'];
        $params[$paramName] = $condition['value'];
    }

    /**
     * @param array $condition
     * @return string
     */
    protected function factoryParamName(array $condition) {
        return uniqid($condition['columnName'] . '_');
    }

    /**
     * @param array $condition
     * @param $query
     * @param $params
     */
    public function addConditionToQuery(array $condition, QueryBuilder $query, array &$params) {
        $condition['paramName'] = $this->factoryParamName($condition);
        $query->andWhere($this->buildExpressionFromCondition($condition));
        $this->addParam($condition, $params);
    }

    public function convertDataColumnForColumn($dataColumnName) {
        try {
            $column = $this->dataSourceConfig->getColumnByDataColumn($dataColumnName);
        } catch (Exception $e) {
            $column = $dataColumnName;
        }
        return $column;
    }

    /**
     * Retrieve a operator from it's string from.
     *
     * @param string        $stringRepresentation
     * @param Configuration $dataSourceConfig
     *
     * @return static
     */
    public static function factoryFromStringRepresentation($stringRepresentation, Configuration $dataSourceConfig) {
        $instance = null;
        if (isset(static::$operatorMapping[$stringRepresentation])) {
            $instance = new static::$operatorMapping[$stringRepresentation]($dataSourceConfig);
        } else {
            $instance = new static($stringRepresentation, $dataSourceConfig);
        }

        return $instance;
    }
}