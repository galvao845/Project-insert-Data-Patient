<?php
namespace tests\Zeedhi\Framework\DB\StoredProcedure\Strategies;

use Zeedhi\Framework\DB\StoredProcedure\Strategies\SQLServerStrategy;
use Zeedhi\Framework\DB\StoredProcedure\StoredProcedure;
use Zeedhi\Framework\DB\StoredProcedure\Param;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\SQLSrv\SQLSrvStatement;

class SQLServerStrategyTest extends \PHPUnit\Framework\TestCase {

    public function setUp() {
        if (!defined('SQLSRV_PARAM_IN')) {
            $this->markTestSkipped("Missing SQL Server extension.");
        }

        $this->connection = $this->getMockBuilder(Connection::class)
                                 ->setMethods(array('prepare'))
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->sqlServerStrategy = new SQLServerStrategy($this->connection);
    }

    public function testExecuteProcedureWithParamInAndOut() {
        $params = array(
            new Param('param1', Param::PARAM_INPUT, 'value'),
            new Param('param2', Param::PARAM_OUTPUT, null, Param::PARAM_TYPE_STR)
        );
        $procedure = new StoredProcedure($this->connection, 'procedureTest', $params);

        $statement = $this->getMockBuilder(SQLSrvStatement::class)
                          ->setMethods(array('execute', 'fetchAll'))
                          ->disableOriginalConstructor()
                          ->getMock();

        $this->connection->expects($this->once())
                         ->method('prepare')
                         ->with('DECLARE @param2 nvarchar; EXECUTE procedureTest ? , @param2 OUTPUT ; SELECT @param2;')
                         ->will($this->returnValue($statement));

        $statement->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($params) {
                $valid = $params[0] == 'value'
                    && $params[1] == null;

                return $valid;
            }))
            ->will($this->returnValue(true));

        $statement->expects($this->once())
            ->method('fetchAll')
            ->with($this->callback(function($fetchMode = null, $fetchArgument = null, $ctorArgs = null){
                return \PDO::FETCH_NUM;
            }))
            ->willReturn(array(array("output value")));

        $outputParams = $this->sqlServerStrategy->executeProcedure($procedure);

        $this->assertCount(1, $outputParams);

        $this->assertArrayHasKey('param2', $outputParams[0]);

        $this->assertEquals($outputParams[0]['param2'], 'output value');
    }

}