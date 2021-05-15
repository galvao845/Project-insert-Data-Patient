<?php
namespace Zeedhi\Framework\DataSource\SyncEngine\Sequence;

use Zeedhi\Framework\DataSource\Configuration;
use Zeedhi\Framework\DataSource\SyncEngine\SyncEngine;

class SyncEngineImpl extends SyncEngine {

    /**
     * Generate a server id for given row.
     *
     * @param Configuration $configuration The metadata about row to be sync.
     * @param array $row The row to be sync.
     * @param string|int $userId The user session identifier.
     *
     * @return mixed
     */
    protected function getNewServerId(Configuration $configuration, $row, $userId) {
        // When using sequence you should get to EntityManager with null values in Id field.
        return null;
    }

    public function postStoreId(Configuration $configuration, $row, $userId, $serverId) {
        $rowHash = $this->getRowHash($configuration, $row, $userId);
        $this->storeId($rowHash, $serverId);
    }
} 