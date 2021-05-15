<?php
namespace tests\Zeedhi\Framework\Socket\Server;

use Zeedhi\Framework\Socket\Server\Payload;

class PayloadTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider providerPayloadData
	 */
	public function testCreatePayloadFromJson($payloadData) {
		$payload = Payload::createFromJson(json_encode($payloadData));
		$this->assertPayload($payload);
	}

	/**
	 * @dataProvider providerPayloadData
	 */
	public function testCreatePayloadFromArray($payloadData) {
		$payload = Payload::createFromArray($payloadData);
		$this->assertPayload($payload);
	}

	/**
	 * @dataProvider providerPayloadData
	 */
	public function testInvalidPayload($payloadData) {
		unset($payloadData['data']);
		$this->assertFalse(Payload::isValid($payloadData));
	}

	public function providerPayloadData() {
		return array(
			array(
				array(
					'event' => 'socket.auth.request',
					'data' => array(
						'userId' => 'b54aec7aa025d07993c1e95ce57fce91',
						'command' => 'commit -m "wtf"'
					)
				)
			)
		);
	}

	private function assertPayload(Payload $payload) {
		$this->assertInstanceOf('Zeedhi\Framework\Socket\Server\Payload', $payload);
		$this->assertEquals('socket.auth.request', $payload->getEvent());
		$json = '{"event":"socket.auth.request","data":{"userId":"b54aec7aa025d07993c1e95ce57fce91","command":"commit -m \"wtf\""}}';
		$this->assertJson($payload->encode());
		$this->assertJsonStringEqualsJsonString($json, $payload->encode());
		$this->assertArrayHasKey('userId', $payload->getData());
		$this->assertArrayHasKey('command', $payload->getData());
	}
}
