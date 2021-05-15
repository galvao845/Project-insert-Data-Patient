<?php
namespace Zeedhi\Framework\Socket\Server;

use Ratchet\Http\HttpServer;
use Zeedhi\Framework\Socket\Server\Exception\TimerAlreadyAddedException;
use Zeedhi\Framework\Socket\Server\Loop\PeriodicTimerInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

/**
 * Class Factory
 *
 * @package Zeedhi\Framework\Socket\Server
 */
class Factory {
	/**
	 * @var string
	 */
	const ADDRESS = '0.0.0.0';

	/**
	 * @var int
	 */
	const PORT = 8080;

	/**
	 * @var string
	 */
	protected $address;

	/**
	 * @var int
	 */
	protected $port;

	/**
	 * @var Bridge
	 */
	protected $bridge;

	/**
	 * @var PeriodicTimerInterface[]
	 */
	protected $periodicTimers;

	/**
	 * @var IoServer
	 */
	protected $server;

	/**
	 * Constructor
	 *
	 * @param Bridge $bridge  The application stack to host
	 * @param int    $port    The port to server sockets on
	 * @param string $address The address to receive sockets on (0.0.0.0 means receive connections from any)
	 */
	public function __construct(Bridge $bridge, $port = self::PORT, $address = self::ADDRESS) {
		$this->bridge = $bridge;
		$this->port = $port;
		$this->address = $address;
		$this->periodicTimers = array();
	}

	/**
	 * Adds a periodic timer to the loop. Throws TimerAlreadyAddedException when the timer was already added to the
	 * event loop.
	 *
	 * @param PeriodicTimerInterface $periodicTimer
	 *
	 * @throws TimerAlreadyAddedException
	 * @return Factory
	 */
	public function addPeriodicTimer(PeriodicTimerInterface $periodicTimer) {
		if (array_key_exists($periodicTimer->getName(), $this->periodicTimers)) {
			throw new TimerAlreadyAddedException();
		}

		$this->periodicTimers[$periodicTimer->getName()] = $periodicTimer;

		return $this;
	}

	/**
	 * Run the application by entering the event loop
	 *
	 * @throws \RuntimeException If a loop was not previously specified
	 * @codeCoverageIgnore
	 */
	public function run() {
		$this->server = IoServer::factory(
			new HttpServer(
				new WsServer($this->bridge)
			),
			$this->getPort(),
			$this->getAddress()
		);
		$this->configure($this->server);
		$this->server->run();
	}

	/**
	 * Configures the io server
	 *
	 * @param IoServer $server
	 * @codeCoverageIgnore
	 */
	protected function configure(IoServer $server) {
		foreach ($this->periodicTimers as $periodicTimer) {
			$server->loop->addPeriodicTimer($periodicTimer->getInterval(), $periodicTimer->getCallback());
		}
	}

	/**
	 * Sets the address to receive sockets
	 *
	 * @param string $address
	 *
	 * @return Factory
	 */
	public function setAddress($address) {
		$this->address = $address;

		return $this;
	}

	/**
	 * Returns the address to receive sockets
	 *
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets the port to server sockets
	 *
	 * @param int $port
	 *
	 * @return Factory
	 */
	public function setPort($port) {
		$this->port = $port;

		return $this;
	}

	/**
	 * Returns the port to server sockets on
	 *
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}
}