<?php
namespace tests\Zeedhi\Framework\DataSource\Manager;

use Zeedhi\Framework\DataSource\FilterCriteria;

class FilterCriteriaTest extends \PHPUnit\Framework\TestCase
{

    protected $conditions = array(array('columnName' => 'REGION_ID',
                                        'operator'   => FilterCriteria::EQ,
                                        'value'      => 4));

    public function testFilterCriteriaWithConditionals()
    {
        $filterCriteria = new FilterCriteria('REGIONS', $this->conditions);
        $this->assertInstanceOf('Zeedhi\Framework\DataSource\FilterCriteria', $filterCriteria, 'It is expected a instance of FilterCriteria');
        $this->assertEquals('REGIONS', $filterCriteria->getDataSourceName(), 'It is expected dataSourceName equals "REGIONS"');
        $this->assertEquals($this->conditions, $filterCriteria->getConditions(), 'It is expected conditionals contain a "$conditionals"');
        $filterCriteria->addCondition('REGION_NAME', FilterCriteria::NEQ, 'Africa');
        $this->assertCount(2, $filterCriteria->getConditions(), 'It is expected conditionals contain two conditionals');
        $this->assertEquals(300, $filterCriteria->getPageSize(), 'It is expected default page size 300');
        $this->assertFalse($filterCriteria->isPaginated(), 'It is expected paginated false');
    }

    public function testFilterCriteriaWithCustomPagination()
    {
        $filterCriteria = new FilterCriteria('REGIONS', $this->conditions, 1, 500);
        $this->assertEquals(500, $filterCriteria->getPageSize(), 'It is expected custom page size 500');
        $filterCriteria->setPageSize(700);
        $this->assertEquals(700, $filterCriteria->getPageSize(), 'It is expected custom page size 700');
        $this->assertEquals(1, $filterCriteria->getPage(), 'It is expected page value equals 1');
        $this->assertTrue($filterCriteria->isPaginated(), 'It is expected paginated true');
    }

    public function testSetDataSourceNameAfterDelay() {
        $filterCriteria = new FilterCriteria('', $this->conditions, 1, 500);
        $filterCriteria->setDataSourceName('REGIONS');
        $this->assertEquals('REGIONS', $filterCriteria->getDataSourceName(), 'It is expected dataSourceName equals "REGIONS"');
    }

    public function testOrderByInFilterCriteria() {
        $filterCriteria = new FilterCriteria('REGIONS');
        $filterCriteria->addOrderBy("REGION_NAME", FilterCriteria::ORDER_ASC);
        $orderBy = $filterCriteria->getOrderBy();
        $this->assertTrue(is_array($orderBy), "Order by must be a array.");
        $this->assertArrayHasKey('REGION_NAME', $orderBy, "Order by must be indexed by column names");
        $this->assertEquals(FilterCriteria::ORDER_ASC, $orderBy['REGION_NAME'], "Order by values must be ASC or DESC.");
    }

    public function testGroupByInFilterCriteria() {
        $filterCriteria = new FilterCriteria('REGIONS');
        $filterCriteria->addGroupBy("REGION_NAME");
        $groupBy = $filterCriteria->getGroupBy();
        $this->assertTrue(is_array($groupBy), "Group by must be a array.");
        $this->assertContains('REGION_NAME', $groupBy, "Group by have a column 'REGION_NAME'");
    }

    public function testAddCondition() {
        $filterCriteria = new FilterCriteria('REGIONS');
        $filterCriteria->addCondition('REGION_NAME', FilterCriteria::IS_NULL, 'value');
        $conditions = $filterCriteria->getConditions();
        $this->assertCount(1, $conditions);
        $condition = $conditions[0];
        $this->assertEquals('REGION_NAME', $condition['columnName']);
        $this->assertEquals(FilterCriteria::IS_NULL, $condition['operator']);
        $this->assertEquals('value', $condition['value']);
    }

    public function testAddConditionWithOutOperator() {
        $filterCriteria = new FilterCriteria('REGIONS');
        $filterCriteria->addCondition('REGION_NAME', 'value');
        $conditions = $filterCriteria->getConditions();
        $this->assertCount(1, $conditions);
        $condition = $conditions[0];
        $this->assertEquals('REGION_NAME', $condition['columnName']);
        $this->assertEquals(FilterCriteria::EQ, $condition['operator']);
        $this->assertEquals('value', $condition['value']);
    }

    public function testAddConditionWithNullValues() {
        $filterCriteria = new FilterCriteria('REGIONS');
        $filterCriteria->addCondition('REGION_NAME', FilterCriteria::IS_NULL, null);
        $conditions = $filterCriteria->getConditions();
        $this->assertCount(1, $conditions);
        $condition = $conditions[0];
        $this->assertEquals('REGION_NAME', $condition['columnName']);
        $this->assertEquals(FilterCriteria::IS_NULL, $condition['operator']);
        $this->assertNull($condition['value']);
    }
}
