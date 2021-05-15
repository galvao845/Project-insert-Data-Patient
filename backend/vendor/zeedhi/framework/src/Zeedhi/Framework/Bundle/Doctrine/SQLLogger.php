<?php
namespace Zeedhi\Framework\Bundle\Doctrine;

use Zeedhi\Framework\Log\AbstractLogger;

class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger{

    /** @var AbstractLogger */
    protected $logger;
    /** @var int */
    protected $lastTimeQueryStart;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null) {
        $this->logger->debug(
            "Executed query {sql} with params {params} and types {types}",
            array(
                'sql' => $sql,
                'params' => var_export($params, true),
                'types'  => var_export($types, true)
            )
        );

        $this->lastTimeQueryStart = microtime();
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery() {
        $this->logger->debug('Last query stopped. It runs in {timespent} micro secs.', array(
            'timespent' => microtime() - $this->lastTimeQueryStart
        ));
    }
}