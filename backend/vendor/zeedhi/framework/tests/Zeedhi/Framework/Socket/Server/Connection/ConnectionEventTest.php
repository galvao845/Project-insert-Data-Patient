<?php
namespace tests\Zeedhi\Framework\Socket\Server\Connection;


use Zeedhi\Framework\Socket\Server\Event\ConnectionEvent;
use Zeedhi\Framework\Socket\Server\Payload;

class ConnectionEventTest extends \PHPUnit\Framework\TestCase {

	public function testCreateEvent() {
		$connectionMockBuilder = $this->getMockBuilder('Zeedhi\Framework\Socket\Server\Connection\Connection');
		$connectionMockBuilder->disableOriginalConstructor();
		$payload = Payload::createFromJson('{"event":"socket.auth.request","data":{"userId":"b54aec7aa025d07993c1e95ce57fce91","command":"commit -m \"wtf\""}}');
		$connectionEvent = new ConnectionEvent($connectionMockBuilder->getMock(), $payload);
		$this->assertInstanceOf('Zeedhi\Framework\Socket\Server\Connection\ConnectionInterface', $connectionEvent->getConnection());
		$this->assertInstanceOf('Zeedhi\Framework\Socket\Server\Payload', $connectionEvent->getPayload());
	}
}
