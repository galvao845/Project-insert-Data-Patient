<?php
namespace Zeedhi\Framework\Socket\Server\Connection;

use Zeedhi\Framework\Socket\Server\Client\ClientInterface;
use Zeedhi\Framework\Socket\Server\Payload;
use Ratchet\ConnectionInterface as RatchetConnectionInterface;

/**
 * Class Connection
 *
 * @package Zeedhi\Framework\Socket\Connection
 */
class Connection implements ConnectionInterface {
	/**
	 * @var ConnectionManagerInterface
	 */
	protected $connectionManager;

	/**
	 * @var \Ratchet\ConnectionInterface
	 */
	protected $connection;

	/**
	 * @var ClientInterface
	 */
	protected $client;

	/**
	 * Constructor
	 *
	 * @param ConnectionManagerInterface $connectionManager
	 * @param RatchetConnectionInterface $connection
	 */
	function __construct(ConnectionManagerInterface $connectionManager, RatchetConnectionInterface $connection) {
		$this->connectionManager = $connectionManager;
		$this->connection = $connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId() {
		return $this->connection->resourceId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRemoteAddress() {
		return $this->connection->remoteAddress;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClient(ClientInterface $client) {
		$this->client = $client;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * {@inheritdoc}
	 */
	public function emit(Payload $payload, ClientInterface $client = null) {
		if ($client !== null) {
			foreach ($this->connectionManager->getConnections() as $connection) {
				if ($connection->getClient() === $client) {
					$connection->emit($payload);
				}
			}
		} else {
			$this->connection->send($payload->encode());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function broadcast(Payload $payload) {
		foreach ($this->connectionManager->getConnections() as $connection) {
			$connection->emit($payload);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function close() {
		$this->connection->close();
	}
}