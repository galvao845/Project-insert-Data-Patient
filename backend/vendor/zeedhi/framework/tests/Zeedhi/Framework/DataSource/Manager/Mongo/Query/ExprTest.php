<?php
namespace tests\Zeedhi\Framework\DataSource\Manager\Mongo\Query;

use Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr;


class ExprTest extends \PHPUnit\Framework\TestCase {

    protected $expr;

    public function setUp() {
        $this->expr = new Expr();
    }

    public function testField() {
        $fieldValue = 'field';
        $result = $this->expr->field($fieldValue);

        $this->assertAttributeSame($fieldValue, 'currentField', $this->expr);
        $this->assertSame($result, $this->expr);
    }

    public function testGetQuery() {
        $expected = 'array';
        $result = $this->expr->getQuery();
        $this->assertInternalType($expected, $result);
    }

    public function testSetArrayOnQuery() {
        $query = array(
            'query' => 'valor'
        );

        $result = $this->expr->setQuery($query);
        $this->assertSame($query, $this->expr->getQuery());
    }

    public function testEquals() {
        $expected = array(
            'field' => array('$eq' => 3)
        );
        $result = $this->expr->field('field')->equals(3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testEqualsArray() {
        $expected = array(
            '$eq' => array (1, 2, 3)
        );

        $result = $this->expr->equals(array(1, 2, 3));

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testNotEquals() {
        $expected = array(
            'field' => array('$ne' => 3)
        );

        $result = $this->expr->field('field')->notEquals(3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testLessThan() {
        $expected = array(
            'field' => array('$lt' => 3)
        );

        $result = $this->expr->field('field')->lt(3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testLessThanOrEquals() {
        $expected = array(
            'field' => array('$lte' => 3)
        );

        $result = $this->expr->field('field')->lte(3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testGreaterThanEquals() {
        $expected = array(
            'field' => array('$gt' => 3)
        );

        $result = $this->expr->field('field')->gt(3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testGreaterThanOrEquals() {
        $expected = array(
            'field' => array('$gte' => 3)
        );

        $result = $this->expr->field('field')->gte(3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testIn() {
        $expected = array(
            'field' => array('$in' => array(1,2))
        );

        $result = $this->expr->field('field')->in(array(1,2));

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testSetStringOnQuery() {
        if(version_compare(PHP_VERSION, '7.0.0') > 0){
            $this->markTestSkipped("This test doesn't on versions above 7.0.0 because the error lauched on version 7.0.0 is not treated.");
        }
        $this->expectException('PHPUnit_Framework_Error');
        $this->expr->setQuery('query');
    }

    public function testInPassingNotArray() {
        if(version_compare(PHP_VERSION, '7.0.0') > 0){
            $this->markTestSkipped("This test doesn't on versions above 7.0.0 because the error lauched on version 7.0.0 is not treated.");
        }
        $this->expectException('PHPUnit_Framework_Error');
        $result = $this->expr->field('field')->in(1);
    }

    public function testNinPassingNotArray() {
        if(version_compare(PHP_VERSION, '7.0.0') > 0){
            $this->markTestSkipped("This test doesn't on versions above 7.0.0 because the error lauched on version 7.0.0 is not treated.");
        }
        $this->expectException('PHPUnit_Framework_Error');
        $result = $this->expr->field('field')->nin(1);
    }

    public function testNin() {
        $expected = array(
            'field' => array('$nin' => array(1,2))
        );

        $result = $this->expr->field('field')->nin(array(1,2));

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testBetween() {
        $expected = array(
            'field' => array(
                '$gte' => 6,
                '$lte' => 3
            )
        );

        $result = $this->expr->field('field')->range(6,3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

    public function testMultipleConditios() {
        $expected = array(
            'field' => array(
                '$eq' => array(1,2),
                '$gte' => 6,
                '$lte' => 3
            )
        );

        $this->expr->field('field')->equals(array(1,2));
        $result = $this->expr->field('field')->range(6,3);

        $query = $this->expr->getQuery();

        $this->assertInstanceOf('Zeedhi\Framework\DataSource\Manager\Mongo\Query\Expr', $result);
        $this->assertSame($expected, $query);
    }

}