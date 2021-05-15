<?php
namespace Zeedhi\Framework\DB\StoredProcedure\Strategies;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Zeedhi\Framework\DB\StoredProcedure\DataBaseStrategy;
use Zeedhi\Framework\DB\StoredProcedure\Param;
use Zeedhi\Framework\DB\StoredProcedure\StoredProcedure;

class SQLServerStrategy implements DataBaseStrategy{

    /** @var Connection */
    protected $connection;

    /** @var array $types */
    protected $types = array(
        0 => null,
	    1 => 'int',
	    2 => 'nvarchar',
	    3 => 'char',
	    4 => 'nvarchar',
	    5 => 'bit',
    );
    
    /**
     * @param Connection $connection
     * 
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * Get the values from the procedure.
     * 
     * Fecthes all rows by key using associative array and re-indexes to array of rows.
     * 
     * @param Doctrine\DBAL\Statement $stmt
     * 
     * @return array $result The result by rows.
     * 
     */
    protected function handleAssociativeFetch($stmt){
        $result = array();
        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);
        foreach($rows as $index=>$row){
            $result[$index] = array();
            foreach($row as $key => $value){
                $result[$index][$key] = $value;
            }
        }
        return $result;        
    }
    
    /**
     * Get the values from the procedure.
     * 
     * Fecthes all rows as array and numeric indexes them.
     * 
     * @param Doctrine\DBAL\Statement $stmt
     * @param array $outputValues
     * 
     * @return array $result The result rows.
     * 
     */
    protected function handleNumericFetch($stmt, $outputValues){
        $result = array();
        $rows = $stmt->fetchAll(FetchMode::NUMERIC);

        foreach($rows as $index=>$row){
            $result[$index] = array();
            foreach($row as $key => $value){
                if(count($outputValues) && isset($outputValues[$key])){
                    $result[$index][$outputValues[$key]] = $value;
                }
            }
        }
        
        return $result;        
    }

    /**
     * Executes the procedure and returns it's returned data after handling.
     * 
     * @param StoredProcedure $procedure   The procedure to be executed.
     * @param array           $paramValues Param values to override values present in Param instance.
     *
     * @return array The output parameters indexed by name.
     * 
     */
    public function executeProcedure(StoredProcedure $procedure, array $paramValues = array()) {
        $queryStmt = $this->getQueryString($procedure);
        $allParams = $procedure->getParams();
        $params = array();
        $outputValues = array();

        foreach ($allParams as $key => $param) {
            $params[] = $param->getValue();
        }

        foreach ($allParams as $key => $param) {
            if($param->isOutput()){
                $outputValues[] = $param->getName();
            }
        }
        
        $stmt = $this->connection->prepare($queryStmt);

        $stmt->execute($params);

        $result = array();
        $result = count($outputValues) ? $this->handleNumericFetch($stmt, $outputValues) : $this->handleAssociativeFetch($stmt);
        
        return $result;
    }

    /**
     * Builds the procedure query.
     * 
     * Build a query to execute, differing between types of returns (select or output parameters).
     * 
     * @param StoredProcedure $procedure
     * 
     * @return string
     *
     */
    protected function getQueryString(StoredProcedure $procedure) {
        $params = $procedure->getParams();
        
        $paramNames = array_map(function(Param $param){
            return "@".$param->getName();
        }, $params);

        $outputParams = array_filter($params, function(Param $param){
            return $param->isOutput();
        });
        $outputParams = is_array($outputParams) && count($outputParams) ? $outputParams : array();

        $paramsStr = array_fill(0, (count($params) - count($outputParams)), "?");
        
        $declareParams = array();
        foreach($paramNames as $index => $name){
            if(isset($outputParams[$index])){
                $declareParams[] = $name;
            }
        }
        
        $paramString = implode(", ", $paramsStr);
        
        $outputStmt = "";
        foreach($declareParams as $index => $name){
            $outputStmt .= $paramString ? "," : "" ;
            $outputStmt .= " $name OUTPUT ";
        }
        $outputStmt .= ";";

        $declareParamsWithTypes = array_map(function($name, Param $param){
            return $name." ".$this->types[$param->getType()];
        }, $declareParams, $outputParams);

        $declareStmt = "";
        if(is_array($declareParamsWithTypes) && count($declareParamsWithTypes)){
            $declareStmt = "DECLARE ".implode(", ", $declareParamsWithTypes).";";
        }

        $executeStmt = " EXECUTE ".$procedure->getName();
        $selectStmt = "";
        $selectStmt .= is_array($declareParams) && count($declareParams) ? " SELECT ".implode(", ", $declareParams).";" : "";

        return $declareStmt.$executeStmt." ".$paramString." ".$outputStmt.$selectStmt;
    }
}