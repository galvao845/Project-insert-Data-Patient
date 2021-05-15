<?php
namespace tests\Zeedhi\Framework\Socket\Server;

use Zeedhi\Framework\Socket\Server\Factory;
use Zeedhi\Framework\Socket\Server\Loop\PeriodicTimerInterface;

class FactoryTest extends \PHPUnit\Framework\TestCase {

	/** @var Factory */
	protected $webSocketMock;
	/** @var PeriodicTimerInterface */
	protected $periodicTimerMock;

	public function setUp() {
		$eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')->getMock();
		$clientProviderMock = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Client\ClientProviderImpl')->getMock();
		$connectionManagerMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Connection\ConnectionManager');
		$connectionManagerMockBuilder->setConstructorArgs(array($clientProviderMock));
		$connectionManagerMock = $connectionManagerMockBuilder->getMock();
		$fileLoggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
		$bridgeMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Bridge');
		$bridgeMockBuilder->setConstructorArgs(array($connectionManagerMock, $eventDispatcherMock, $fileLoggerMock));
		$bridgeMock = $bridgeMockBuilder->getMockForAbstractClass();
		$factoryMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Factory');
		$factoryMockBuilder->enableProxyingToOriginalMethods();
		$factoryMockBuilder->setConstructorArgs(array($bridgeMock));
		$this->webSocketMock = $factoryMockBuilder->getMock();
		$this->periodicTimerMock = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Loop\PeriodicTimerInterface')->getMock();
		$this->periodicTimerMock->expects($this->any())->method('getName')->willReturn('messageToTopConnections');
	}

	public function testSocketFactory() {
		$this->webSocketMock->setPort(9999);
		$this->webSocketMock->setAddress('127.0.0.1');
		$this->webSocketMock->addPeriodicTimer($this->periodicTimerMock);
		$this->assertEquals(9999, $this->webSocketMock->getPort());
		$this->assertEquals('127.0.0.1', $this->webSocketMock->getAddress());
	}

	public function testAlreadyPeriodicTimer() {
		$this->webSocketMock->addPeriodicTimer($this->periodicTimerMock);
		$this->expectException('Zeedhi\Framework\Socket\Server\Exception\TimerAlreadyAddedException');
		$this->webSocketMock->addPeriodicTimer($this->periodicTimerMock);
	}

}
