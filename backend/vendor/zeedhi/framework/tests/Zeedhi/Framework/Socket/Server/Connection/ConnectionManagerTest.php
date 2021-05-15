<?php
namespace tests\Zeedhi\Framework\Socket\Server\Connection;


use Zeedhi\Framework\Socket\Server\Connection\ConnectionInterface;

class ConnectionManagerTest extends \PHPUnit\Framework\TestCase {

	const REMOTE_ADDR = '127.0.0.1';
	const RESOURCE_ID = 1;
	/** @var  \Zeedhi\Framework\Socket\Server\Connection\ConnectionManagerInterface */
	protected $connectionManagerMock;
	/** @var  \Ratchet\ConnectionInterface */
	protected $sockConnectionMock;

	public function setUp() {
		$clientProviderMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Client\ClientProviderImpl');
		$clientProviderMockBuilder->enableProxyingToOriginalMethods();
		$clientProviderMock = $clientProviderMockBuilder->getMock();
		$connectionManagerMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Connection\ConnectionManager');
		$connectionManagerMockBuilder->setConstructorArgs(array($clientProviderMock));
		$connectionManagerMockBuilder->enableProxyingToOriginalMethods();
		$this->connectionManagerMock = $connectionManagerMockBuilder->getMock();
		$sockConnectionMockBuilder = $this->getMockBuilder('Ratchet\ConnectionInterface');
		$sockConnectionMockBuilder->disableOriginalConstructor();
		$this->sockConnectionMock = $sockConnectionMockBuilder->getMock();
		$this->sockConnectionMock->remoteAddress = self::REMOTE_ADDR;
		$this->sockConnectionMock->resourceId = self::RESOURCE_ID;
	}

	private function assertConnection(ConnectionInterface $connection) {
		$this->assertInstanceOf('Zeedhi\Framework\Socket\Server\Connection\ConnectionInterface', $connection);
		$this->assertEquals(self::REMOTE_ADDR, $connection->getRemoteAddress());
		$this->assertEquals(self::RESOURCE_ID, $connection->getId());
	}

	public function testAddConnection() {
		$this->assertConnection($this->connectionManagerMock->addConnection($this->sockConnectionMock));
	}

	public function testGetAllConnections() {
		$this->connectionManagerMock->addConnection($this->sockConnectionMock);
		$this->assertEquals(1, sizeof($this->connectionManagerMock->getConnections($this->sockConnectionMock)));
	}

	public function testGetSpecificConnection() {
		$this->connectionManagerMock->addConnection($this->sockConnectionMock);
		$this->assertConnection($this->connectionManagerMock->getConnection($this->sockConnectionMock));
	}

	public function testAddSameConnections() {
		$this->assertConnection($this->connectionManagerMock->addConnection($this->sockConnectionMock));
		$this->assertFalse($this->connectionManagerMock->addConnection($this->sockConnectionMock));
	}

	public function testCloseConnection() {
		$this->connectionManagerMock->addConnection($this->sockConnectionMock);
		$this->assertConnection($this->connectionManagerMock->closeConnection($this->sockConnectionMock));
		$this->assertFalse($this->connectionManagerMock->hasConnection($this->sockConnectionMock));
	}

	public function testCloseNonExistentConnection() {
		$this->assertNull($this->connectionManagerMock->getConnection($this->sockConnectionMock));
		$this->assertFalse($this->connectionManagerMock->closeConnection($this->sockConnectionMock));
	}
}
