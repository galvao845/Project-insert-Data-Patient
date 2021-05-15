<?php
namespace Zeedhi\Framework\Socket\Server\Connection;

use Zeedhi\Framework\Socket\Server\Exception\NotManagedConnectionException;
use Ratchet\ConnectionInterface as SocketConnection;

/**
 * Interface ConnectionManagerInterface
 *
 * @package Zeedhi\Framework\Socket\Server\Connection
 */
interface ConnectionManagerInterface {
	/**
	 * Returns true if the given socket connection is managed by this manager, false otherwise.
	 *
	 * @param SocketConnection $socketConnection The socket connection to check
	 *
	 * @return boolean True when the given connection is managed, false otherwise.
	 */
	public function hasConnection(SocketConnection $socketConnection);

	/**
	 * Returns the connection for the given socket connection.
	 *
	 * @param SocketConnection $socketConnection
	 *
	 * @return ConnectionInterface
	 */
	public function getConnection(SocketConnection $socketConnection);

	/**
	 * Returns all managed connections.
	 *
	 * @return ConnectionInterface[]
	 */
	public function getConnections();

	/**
	 * Registers the given socket connection if not managed already.
	 *
	 * @param SocketConnection $socketConnection
	 *
	 * @return ConnectionInterface
	 */
	public function addConnection(SocketConnection $socketConnection);

	/**
	 * Closes and removes a managed connection by the given socket connection. Returns the connection that was closed on
	 * success or false otherwise,
	 *
	 * @param SocketConnection $socketConnection
	 *
	 * @return boolean|ConnectionInterface The connection that was closed, false otherwise.
	 */
	public function closeConnection(SocketConnection $socketConnection);

	/**
	 * Authenticates a managed connection. Throws NotManagedConnectionException when the given
	 * connection is not managed by this manager.
	 *
	 * @param ConnectionInterface $connection  The connection to authenticate
	 *
	 * @return boolean True on success, false otherwise
	 * @throws NotManagedConnectionException
	 */
	public function anonymousAuthenticate(ConnectionInterface $connection);
}