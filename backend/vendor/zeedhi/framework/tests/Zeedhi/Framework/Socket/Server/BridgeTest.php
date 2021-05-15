<?php
namespace tests\Zeedhi\Framework\Socket\Server;

use Ratchet\ConnectionInterface;
use tests\Zeedhi\Framework\ApplicationMocks\BridgeImpl;
use Zeedhi\Framework\Socket\Server\Connection\ConnectionManagerInterface;

class BridgeTest extends \PHPUnit\Framework\TestCase {

	/** @var BridgeImpl */
	protected $bridge;
	/** @var ConnectionManagerInterface */
	protected $connectionManagerMock;
	/** @var ConnectionInterface */
	protected $sockConnectionMock;


	public function setUp() {
		$clientProviderMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Client\ClientProviderImpl');
		$clientProviderMockBuilder->enableProxyingToOriginalMethods();
		$clientProviderMock = $clientProviderMockBuilder->getMock();

		$eventDispatcherMockBuilder = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher');
		$eventDispatcherMockBuilder->enableProxyingToOriginalMethods();
		$eventDispatcherMock = $eventDispatcherMockBuilder->getMock();

		$loggerMock = $this->getMockForAbstractClass('Zeedhi\Framework\Log\AbstractLogger');
		$this->sockConnectionMock = $this->getMockBuilder('Ratchet\ConnectionInterface')->getMock();
		$this->sockConnectionMock->remoteAddress = '127.0.0.1';
		$this->sockConnectionMock->resourceId = 1;

		$connectionManagerMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Connection\ConnectionManager');
		$connectionManagerMockBuilder->setConstructorArgs(array($clientProviderMock));
		$connectionManagerMockBuilder->enableProxyingToOriginalMethods();
		$this->connectionManagerMock = $connectionManagerMockBuilder->getMock();

		$this->bridge = new BridgeImpl($this->connectionManagerMock, $eventDispatcherMock, $loggerMock);
	}

	protected function assertDispatchEvent($expected) {
		$this->assertEquals($expected, $this->bridge->isEventCalled());
		$this->bridge->restoreEvent();
	}

	protected function newConnection() {
		$this->bridge->onOpen($this->sockConnectionMock);
		$this->assertEquals(true, $this->connectionManagerMock->hasConnection($this->sockConnectionMock));
		$this->assertDispatchEvent(true);
	}

	protected function closeConnection() {
		$this->bridge->onClose($this->sockConnectionMock);
		$this->assertEquals(false, $this->connectionManagerMock->hasConnection($this->sockConnectionMock));
		$this->assertDispatchEvent(true);
	}

	public function testOnOpen() {
		$this->newConnection();
	}

	public function testOnClose() {
		$this->newConnection();
		$this->closeConnection();
	}

	public function testOnError() {
		$this->newConnection();
		$this->bridge->onError($this->sockConnectionMock, new \RuntimeException());
		$this->assertEquals(false, $this->connectionManagerMock->hasConnection($this->sockConnectionMock));
		$this->assertDispatchEvent(true);
	}

	public function testOnMessage() {
		$this->newConnection();
		$payload = json_encode(array(
			'event' => 'onMessage',
			'data' => array(
				'userId' => 'b54aec7aa025d07993c1e95ce57fce91',
				'command' => 'commit -m "wtf"'
			)
		));
		$this->bridge->onMessage($this->sockConnectionMock, $payload);
		$this->assertDispatchEvent(true);
	}

	public function testOnMessageWithInvalidPayload() {
		$this->newConnection();
		$invalidPayload = json_encode(array(
			'payload' => 'commit'
		));
		$this->bridge->onMessage($this->sockConnectionMock, $invalidPayload);
		$this->assertDispatchEvent(false);
	}

	public function testOnMessageWhenHasNoConnection() {
		$this->bridge->onMessage($this->sockConnectionMock, null);
		$this->assertDispatchEvent(false);
	}

	public function testOnMessageHasNoListener() {
		$this->newConnection();
		$payload = json_encode(array(
			'event' => 'sock.auth.request',
			'data' => array(
				'userId' => 'b54aec7aa025d07993c1e95ce57fce91',
				'command' => 'commit -m "wtf"'
			)
		));
		$this->bridge->onMessage($this->sockConnectionMock, $payload);
		$this->assertDispatchEvent(false);
	}

}