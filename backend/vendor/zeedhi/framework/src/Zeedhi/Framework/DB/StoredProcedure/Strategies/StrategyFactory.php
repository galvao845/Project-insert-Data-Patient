<?php
namespace Zeedhi\Framework\DB\StoredProcedure\Strategies;

use Doctrine\DBAL\Connection;
use Zeedhi\Framework\DB\StoredProcedure\DataBaseStrategy;

class StrategyFactory {

    /**
     * @param Connection $connection
     *
     * @return DataBaseStrategy
     *
     * @throws \Exception
     */
    public static function createStrategy(Connection $connection) {
        $strategy = null;
        switch ($connection->getDriver()->getName()) {
            case 'oci8':
            case 'zeedhi_oci8':
                $strategy = new OracleStrategy($connection);
                break;
            case 'sqlsrv':
                $strategy = new SQLServerStrategy($connection);
                break;
            case 'pdo_dblib':
                $strategy = new DBLibStrategy($connection);
                break;
            default:
                throw Exception::unsupportedDriver($connection->getDriver()->getName());
                break;
        }

        return $strategy;
    }
}