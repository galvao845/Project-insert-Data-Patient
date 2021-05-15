<?php
namespace Zeedhi\Framework\Socket\Server\Event;

use Zeedhi\Framework\Socket\Server\Connection\ConnectionInterface;
use Symfony\Component\EventDispatcher\Event;
use Zeedhi\Framework\Socket\Server\Payload;

/**
 * Class ConnectionEvent
 *
 * @package Zeedhi\Framework\Socket\Server\Event
 */
class ConnectionEvent extends Event {
	/**
	 * @var string
	 */
	const SOCKET_CLOSE = 'socketOnClose';

	/**
	 * @var string
	 */
	const SOCKET_ERROR = 'socketOnError';

	/**
	 * @var string
	 */
	const SOCKET_OPEN = 'socketOnOpen';

	/**
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * @var Payload
	 */
	protected $payload;

	/**
	 * Constructor
	 *
	 * @param ConnectionInterface $connection
	 * @param Payload             $payload
	 */
	public function __construct(ConnectionInterface $connection, Payload $payload = null) {
		$this->connection = $connection;
		$this->payload = $payload;
	}

	/**
	 * Returns the associated connection to the dispatched event
	 *
	 * @return ConnectionInterface
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Returns the associated payload to the dispatched event
	 *
	 * @return Payload
	 */
	public function getPayload() {
		return $this->payload;
	}
}