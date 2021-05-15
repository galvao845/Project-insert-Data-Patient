<?php

use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DataSource\Operator\DefaultOperator;

class OperatorsTest extends PHPUnit\Framework\TestCase {

    const UNIQID_REGEX = '[0-9a-f]{13}';

    /** @var  \Zeedhi\Framework\DataSource\Configuration */
    protected $dataSourceConfig;
    /** @var  \Doctrine\DBAL\Connection */
    protected $connection;

    protected function setUp() {
        $this->dataSourceConfig = new \Zeedhi\Framework\DataSource\Configuration('TEST', array('ID', 'NAME', 'VALUE'));
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = \HumanRelation\Util\EntityManagerFactory::createWithOracleConnection();
        $this->connection = $entityManager->getConnection();
    }

    public function assertBasicOperator($operator) {
        $condition = array('columnName' => 'ID', 'operator' => $operator, 'value' => 123);
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation($operator, $this->dataSourceConfig);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(1, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID {$operator} :{$keys[0]}";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals(123, $params[$keys[0]]);
    }

    public function testBasicOperators() {
        $this->assertBasicOperator(FilterCriteria::EQ);
        $this->assertBasicOperator(FilterCriteria::NEQ);
        $this->assertBasicOperator(FilterCriteria::LT);
        $this->assertBasicOperator(FilterCriteria::LTE);
        $this->assertBasicOperator(FilterCriteria::GT);
        $this->assertBasicOperator(FilterCriteria::GTE);
        $this->assertBasicOperator(FilterCriteria::IS);
    }

    public function testMultipleConditionsOnSameColumn() {
        $condition1 = array('columnName' => 'ID', 'operator' => FilterCriteria::NEQ, 'value' => 1);
        $condition2 = array('columnName' => 'ID', 'operator' => FilterCriteria::IN, 'value' => array(1, 2, 3, 4, 5));
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        DefaultOperator::factoryFromStringRepresentation(FilterCriteria::NEQ, $this->dataSourceConfig)
            ->addConditionToQuery($condition1, $query, $params);
        DefaultOperator::factoryFromStringRepresentation(FilterCriteria::IN, $this->dataSourceConfig)
            ->addConditionToQuery($condition2, $query, $params);
        $this->assertCount(2, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[1]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE (ID <> :".$keys[0].") AND (ID IN(:".$keys[1]."))";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals(1, $params[$keys[0]]);
        $this->assertEquals(array(1, 2, 3, 4, 5), $params[$keys[1]]);
    }

    public function testInOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::IN, 'value' => array(1, 2, 3));
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::IN, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\In', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(1, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID IN(:{$keys[0]})";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals(array(1, 2, 3), $params[$keys[0]]);
    }

    public function testNotInOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::NOT_IN, 'value' => array(1, 2, 3));
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::NOT_IN, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\NotIn', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(1, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID NOT IN(:{$keys[0]})";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertCount(3, $params[$keys[0]]);
        $this->assertEquals(array(1, 2, 3), $params[$keys[0]]);
    }

    public function testLikeOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::LIKE, 'value' => 'A');
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::LIKE, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\Like', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(1, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID LIKE :{$keys[0]}";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals("A", $params[$keys[0]]);
    }

    public function testNotLikeOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::NOT_LIKE, 'value' => 'A');
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::NOT_LIKE, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\NotLike', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(1, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID NOT LIKE :{$keys[0]}";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals("A", $params[$keys[0]]);
    }

    public function testIsNullOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::IS_NULL, 'value' => null);
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::IS_NULL, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\IsNull', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID IS NULL";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertCount(0, $params);
    }

    public function testIsNotNullOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::IS_NOT_NULL, 'value' => null);
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::IS_NOT_NULL, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\IsNotNull', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID IS NOT NULL";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertCount(0, $params);
    }

    public function testBetweenOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::BETWEEN, 'value' => array(1,10));
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::BETWEEN, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\Between', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(2, $params);
        $keys = array_keys($params);
        $this->assertRegExp('/^ID_'.self::UNIQID_REGEX.'_INIT_VALUE$/', $keys[0]);
        $this->assertRegExp('/^ID_'.self::UNIQID_REGEX.'_END_VALUE$/', $keys[1]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID BETWEEN :{$keys[0]} AND :{$keys[1]}";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals(1, $params[$keys[0]]);
        $this->assertEquals(10, $params[$keys[1]]);
    }

    public function testNotBetweenOperator() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::NOT_BETWEEN, 'value' => array(1,10));
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::NOT_BETWEEN, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\NotBetween', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $keys = array_keys($params);
        $this->assertCount(2, $params);
        $this->assertRegExp('/^ID_'.self::UNIQID_REGEX.'_INIT_VALUE$/', $keys[0]);
        $this->assertRegExp('/^ID_'.self::UNIQID_REGEX.'_END_VALUE$/', $keys[1]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE ID NOT BETWEEN :{$keys[0]} AND :{$keys[1]}";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals(1, $params[$keys[0]]);
        $this->assertEquals(10, $params[$keys[1]]);
    }

    public function testLikeAllOperator() {
        $condition = array('columnName' => '*', 'operator' => FilterCriteria::LIKE_ALL, 'value' => 'A');
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::LIKE_ALL, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\LikeAll', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $keys = array_keys($params);
        $this->assertRegExp('/^LIKE_ALL_'.self::UNIQID_REGEX.'$/', $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE LOWER(ID) LIKE LOWER(:{$keys[0]}) OR LOWER(NAME) LIKE LOWER(:{$keys[0]}) OR LOWER(VALUE) LIKE LOWER(:{$keys[0]})";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertCount(1, $params);
        $this->assertEquals('A', $params[$keys[0]]);
    }

    public function testLikeAllOperatorChosenColumns() {
        $condition = array('columnName' => 'ID|NAME', 'operator' => FilterCriteria::LIKE_ALL, 'value' => 'A');
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::LIKE_ALL, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\LikeAll', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $keys = array_keys($params);
        $this->assertRegExp('/^LIKE_ALL_'.self::UNIQID_REGEX.'$/', $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE LOWER(ID) LIKE LOWER(:{$keys[0]}) OR LOWER(NAME) LIKE LOWER(:{$keys[0]})";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertCount(1, $params);
        $this->assertEquals('A', $params[$keys[0]]);
    }

    public function testLikeInsensitive() {
        $condition = array('columnName' => 'ID', 'operator' => FilterCriteria::LIKE_I, 'value' => 'A');
        $query = $this->connection->createQueryBuilder()->select('NAME')->from('TEST');
        $params = array();
        $operatorInstance = DefaultOperator::factoryFromStringRepresentation(FilterCriteria::LIKE_I, $this->dataSourceConfig);
        $this->assertInstanceOf('\Zeedhi\Framework\DataSource\Operator\LikeI', $operatorInstance);
        $operatorInstance->addConditionToQuery($condition, $query, $params);
        $this->assertCount(1, $params);
        $keys = array_keys($params);
        $this->assertRegExp("/^ID_".self::UNIQID_REGEX."$/", $keys[0]);
        $expectedQuery = "SELECT NAME FROM TEST WHERE LOWER(ID) LIKE LOWER(:{$keys[0]})";
        $this->assertEquals($expectedQuery, $query->getSQL());
        $this->assertEquals("A", $params[$keys[0]]);
    }
}
