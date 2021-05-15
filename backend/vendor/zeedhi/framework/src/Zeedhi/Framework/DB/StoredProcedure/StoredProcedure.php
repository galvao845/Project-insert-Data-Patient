<?php
namespace Zeedhi\Framework\DB\StoredProcedure;

use Doctrine\DBAL\Connection;
use Zeedhi\Framework\DB\StoredProcedure\Strategies\StrategyFactory;

/**
 * Class StoredProcedure
 *
 * @package Zeedhi\Framework\DB\Procedure
 */
class StoredProcedure {

	/** @var Connection */
	protected $connection;
	/** @var string */
	protected $name;
	/** @var Param[] */
	protected $params;


	/**
	 * Constructor
	 *
	 * @param Connection $connection    The connection which contains the procedure to be performed
	 * @param string     $procedureName The procedure name
	 * @param array      $params        The params of the procedure
	 */
	public function __construct(Connection $connection, $procedureName, $params = array()) {
		$this->connection = $connection;
		$this->name = $procedureName;
		$this->params = $params;
	}

	/**
	 * Adds the parameters to be passed in the execution of the procedure
	 *
	 * @param Param $param
	 */
	public function addParam(Param $param) {
		$this->params[] = $param;
	}

    /**
     * Performs the procedure in the database and returns the output values
     *
     * @param array $paramValues
     * @return array
     * @throws \Exception
     */
	public function execute(array $paramValues = array()) {
        return StrategyFactory::createStrategy($this->connection)->executeProcedure($this, $paramValues);
	}

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Param[]
     */
    public function getParams()
    {
        return $this->params;
    }
}