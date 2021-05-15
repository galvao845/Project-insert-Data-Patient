<?php
namespace Zeedhi\Framework\Socket\Server;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zeedhi\Framework\Socket\Server\Connection\ConnectionManagerInterface;
use Zeedhi\Framework\Socket\Server\Connection\ConnectionInterface;
use Zeedhi\Framework\Socket\Server\Event\ConnectionEvent;
use Zeedhi\Framework\Socket\Server\Exception\InvalidPayloadException;
use Zeedhi\Framework\Socket\Server\Exception\NotManagedConnectionException;
use Zeedhi\Framework\Socket\Server\Exception\InvalidEventCallException;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface as SocketConnection;
use Ratchet\MessageComponentInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Bridge
 *
 * @package Zeedhi\Framework\Socket\Server
 */
abstract class Bridge implements MessageComponentInterface, EventSubscriberInterface {
	/**
	 * @var ConnectionManagerInterface
	 */
	protected $connectionManager;

	/**
	 * @var EventDispatcher
	 */
	protected $eventDispatcher;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param ConnectionManagerInterface $connectionManager
	 * @param EventDispatcherInterface   $eventDispatcher
	 * @param LoggerInterface            $logger
	 */
	public function __construct(
		ConnectionManagerInterface $connectionManager,
		EventDispatcherInterface $eventDispatcher,
		LoggerInterface $logger
	) {
		$this->connectionManager = $connectionManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->logger = $logger;
		$this->eventDispatcher->addSubscriber($this);
	}

	/**
	 * Triggered when a new connection is opened
	 *
	 * @param SocketConnection $conn The socket/connection that just connected to your application
	 */
	public function onOpen(SocketConnection $conn) {
		$connection = $this->connectionManager->addConnection($conn);
		$this->logger->notice(
			sprintf(
				'New connection <info>#%s</info> (<comment>%s</comment>)',
				$connection->getId(),
				$connection->getRemoteAddress()
			)
		);
		$this->handleAuthentication($connection);
		$response = new Payload(ConnectionEvent::SOCKET_OPEN, $connection->getClient()->jsonSerialize());
		$this->dispatchEvent($connection, $response);
	}

	/**
	 * This is called before or after a socket is closed (depends on how it's closed)
	 *
	 * @param SocketConnection $conn The socket/connection that is closing/closed
	 *
	 * @throws \Exception
	 */
	public function onClose(SocketConnection $conn) {
		$connection = $this->connectionManager->closeConnection($conn);
		if ($connection instanceof ConnectionInterface) {
			$this->logger->notice(
				sprintf(
					'Closed connection <info>#%s</info> (<comment>%s</comment>)',
					$connection->getId(),
					$connection->getRemoteAddress()
				)
			);
			$response = new Payload(ConnectionEvent::SOCKET_CLOSE, $connection->getClient()->jsonSerialize());
			$this->dispatchEvent($connection, $response);
		}
	}

	/**
	 * Triggered when if there is an error with one of the sockets,
	 * or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server
	 * and bubbled back up the application this method
	 *
	 * @param SocketConnection $conn
	 * @param \Exception       $e
	 */
	public function onError(SocketConnection $conn, \Exception $e) {
		$connection = $this->connectionManager->closeConnection($conn);
		$response = new Payload(ConnectionEvent::SOCKET_ERROR, $connection->getClient()->jsonSerialize());
		$this->dispatchEvent($connection, $response);
		$this->logger->error($e->getMessage());
	}

	/**
	 * Triggered when a client sends data through the socket
	 *
	 * @param SocketConnection $from The socket/connection that sent the message to your application
	 * @param string           $msg  The message received
	 *
	 * @throws \Exception
	 */
	public function onMessage(SocketConnection $from, $msg) {
		try {
			if (!$this->connectionManager->hasConnection($from)) {
				throw new NotManagedConnectionException('Unknown Connection');
			}

			$payload = Payload::createFromJson($msg);
			if ($payload === null) {
				throw new InvalidPayloadException(sprintf('Invalid payload received: "%s"', $msg));
			}

			$connection = $this->connectionManager->getConnection($from);
			$this->dispatchEvent($connection, $payload);

		} catch (InvalidPayloadException $e) {
			$this->logger->debug($e->getMessage());
		} catch (NotManagedConnectionException $e) {
			$this->logger->warning($e->getMessage());
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
			throw new \RuntimeException('An error occurred during server runtime.', 500, $e);
		}
	}


	/**
	 * Handles the the given payload received by the given connection.
	 *
	 * @param ConnectionInterface $connection
	 * @param Payload             $payload
	 */
	protected function dispatchEvent(ConnectionInterface $connection, Payload $payload) {
		if ($this->eventDispatcher->hasListeners($payload->getEvent())) {
			$this->eventDispatcher->dispatch($payload->getEvent(), new ConnectionEvent($connection, $payload));
			$this->logger->notice(sprintf('Dispatched event: %s', $payload->getEvent()));
		}
	}

	/**
	 * Handles the connection authentication.
	 *
	 * @param ConnectionInterface $connection
	 */
	protected function handleAuthentication(ConnectionInterface $connection) {
		$this->connectionManager->anonymousAuthenticate($connection);
		$this->logger->notice(
			sprintf(
				'Authenticated <info>#%s</info> (<comment>%s</comment>)',
				$connection->getId(),
				$connection->getRemoteAddress()
			)
		);
	}
}