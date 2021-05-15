<?php
namespace tests\Zeedhi\Framework\DB\StoredProcedure;

use Zeedhi\Framework\DB\StoredProcedure\Param;

class ParamTest extends \PHPUnit\Framework\TestCase {

    public function testGetValueBoolean() {
        $param = new Param("Param1", Param::PARAM_INPUT, true, Param::PARAM_TYPE_BOOL);
        $this->assertEquals("Param1", $param->getName());
        $this->assertTrue($param->getValue());
    }

    public function testGetValueNumeric() {
        $param = new Param("Param1", Param::PARAM_INPUT, '10', Param::PARAM_TYPE_INT);
        $this->assertEquals("Param1", $param->getName());
        $this->assertEquals(10, $param->getValue());
    }

    public function testGetValueUntyped() {
        $param = new Param("Param1", Param::PARAM_INPUT, '10');
        $this->assertEquals("Param1", $param->getName());
        $this->assertEquals('10', $param->getValue());
    }
}
