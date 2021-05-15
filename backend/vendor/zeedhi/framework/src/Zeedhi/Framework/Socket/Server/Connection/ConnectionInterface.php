<?php
namespace Zeedhi\Framework\Socket\Server\Connection;

use Zeedhi\Framework\Socket\Server\Client\ClientInterface;
use Zeedhi\Framework\Socket\Server\Payload;

/**
 * Interface ConnectionInterface
 *
 * @package Zeedhi\Framework\Socket\Server\Connection
 */
interface ConnectionInterface {
	/**
	 * Returns the resource identifier for this connection
	 *
	 * @return int
	 */
	public function getId();

	/**
	 * Returns the remote address of this connection.
	 *
	 * @return string
	 */
	public function getRemoteAddress();

	/**
	 * Sets the client for this connection.
	 *
	 * @param ClientInterface $client
	 *
	 * @return ConnectionInterface
	 */
	public function setClient(ClientInterface $client);

	/**
	 * Returns the client for this connection.
	 *
	 * @return ClientInterface
	 */
	public function getClient();

	/**
	 * Sends the given payload to this connection.
	 *
	 * @param Payload         $payload
	 * @param ClientInterface $client
	 *
	 * @return mixed
	 */
	public function emit(Payload $payload, ClientInterface $client = null);

	/**
	 * Emits the given payload to all managed connections.
	 *
	 * @param Payload $payload
	 *
	 * @return void
	 */
	public function broadcast(Payload $payload);

	/**
	 * Close the connection
	 *
	 * @return void
	 */
	public function close();
}