<?php
namespace Zeedhi\Framework\DataSource\SyncEngine;

use Doctrine\DBAL\Connection;
use Zeedhi\Framework\Cache\Cache;
use Zeedhi\Framework\DataSource\Configuration;

class MaxPlus1SyncEngineImpl extends SyncEngine {

	//@todo create a Zeedhi Connection Interface
	/** @var Connection */
	protected $connection;

	public function __construct(Cache $cache, Connection $connection) {
		parent::__construct($cache);
		$this->connection = $connection;
	}

	protected function getNewServerId(Configuration $configuration, $row, $userId) {
		$sequentialColumnName = $configuration->getSequentialColumn();
		$tableName = $configuration->getTableName();
		$sql = "select max({$sequentialColumnName}) + 1 from {$tableName}";

		$params = array();
		$conditions = array();
		foreach ($configuration->getPrimaryKeyColumns() as $pkColumnName) {
			if ($pkColumnName !== $sequentialColumnName) {
				$params[$pkColumnName] = $row[$pkColumnName];
				$conditions[] = "$pkColumnName = :$pkColumnName";
			}
		}

		if (count($conditions)) {
			$sql .= " where " . implode(" and ", $conditions);
		}

		return $this->connection->fetchColumn($sql, $params);
	}
}