<?php
namespace Zeedhi\Framework\Socket\Server\Connection;

use Zeedhi\Framework\Socket\Server\Client\ClientProviderInterface;
use Zeedhi\Framework\Socket\Server\Exception\NotManagedConnectionException;
use Ratchet\ConnectionInterface as SocketConnection;

/**
 * Class ConnectionManager
 *
 * @package Zeedhi\Framework\Socket\Server\Connection
 */
class ConnectionManager implements ConnectionManagerInterface {
	/**
	 * @var ClientProviderInterface
	 */
	protected $clientProvider;

	/**
	 * @var ConnectionInterface[]
	 */
	protected $connections;

	/**
	 * @param ClientProviderInterface $clientProvider
	 */
	public function __construct(ClientProviderInterface $clientProvider) {
		$this->clientProvider = $clientProvider;
		$this->connections = array();
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasConnection(SocketConnection $socketConnection) {
		return isset($this->connections[$socketConnection->resourceId]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConnection(SocketConnection $socketConnection) {
		if (!$this->hasConnection($socketConnection)) {

			return null;
		}

		return $this->connections[$socketConnection->resourceId];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConnections() {
		return $this->connections;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addConnection(SocketConnection $socketConnection) {
		if (!$this->hasConnection($socketConnection)) {
			$connection = new Connection($this, $socketConnection);
			$this->connections[$connection->getId()] = $connection;
			return $connection;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function closeConnection(SocketConnection $socketConnection) {
		if (!$this->hasConnection($socketConnection)) {
			return false;
		}

		$connection = $this->getConnection($socketConnection);
		$connection->close();

		unset($this->connections[$connection->getId()]);

		return $connection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function anonymousAuthenticate(ConnectionInterface $connection) {
		if (!isset($this->connections[$connection->getId()])) {
			throw new NotManagedConnectionException();
		}

		$client = $this->clientProvider->findByAccessToken();
		if ($client !== null) {
			$connection->setClient($client);
			return true;
		}

		return false;
	}
}