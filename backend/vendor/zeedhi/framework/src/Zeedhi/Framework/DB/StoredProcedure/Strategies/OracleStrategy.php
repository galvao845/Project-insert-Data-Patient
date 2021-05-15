<?php
namespace Zeedhi\Framework\DB\StoredProcedure\Strategies;

use Doctrine\DBAL\Connection;
use Zeedhi\Framework\DB\StoredProcedure\DataBaseStrategy;
use Zeedhi\Framework\DB\StoredProcedure\Param;
use Zeedhi\Framework\DB\StoredProcedure\StoredProcedure;

class OracleStrategy implements DataBaseStrategy {

    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     *
     *
     * @param StoredProcedure $procedure The procedure to be executed.
     * @param array $paramValues Param values to override values present in Param instance.
     *
     * @return array The output parameters indexed by name.
     */
    public function executeProcedure(StoredProcedure $procedure, array $paramValues = array()) {
        $queryStmt = $this->getQueryString($procedure);
        $stmt = $this->connection->prepare($queryStmt);
        $valuesOutput = array();
        foreach ($procedure->getParams() as $param) {
            if ($param->isOutput()) {
                $valuesOutput[$param->getName()] = $param->getValue();
                $stmt->bindParam($param->getParamAlias(), $valuesOutput[$param->getName()], $param->getType(), $param->getLength());
            } else {
                $stmt->bindValue($param->getParamAlias(), $param->getValue(), $param->getType());
            }
        }

        $stmt->execute();
        return $valuesOutput;
    }

    /**
     * @param StoredProcedure $procedure
     * @return string
     */
    protected function getQueryString(StoredProcedure $procedure) {
        $paramNames = array_map(function (Param $param) {
            return ":" . $param->getName();
        }, $procedure->getParams());
        $paramString = implode(", ", $paramNames);
        return "CALL " . $procedure->getName() . " ( " . $paramString . " )";
    }
}