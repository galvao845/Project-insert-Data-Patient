<?php
namespace tests\Zeedhi\Framework\HTTP;


use Zeedhi\Framework\HTTP\Server;

class ServerTest extends \PHPUnit\Framework\TestCase {

	public function testShouldExtractHeadersFromServerArray() {
		$server = array(
			'SOME_SERVER_VARIABLE' => 'value',
			'SOME_SERVER_VARIABLE2' => 'value',
			'ROOT' => 'value',
			'HTTP_CONTENT_TYPE' => 'text/html',
			'HTTP_CONTENT_LENGTH' => '0',
			'HTTP_ETAG' => 'asdf'
		);
		$server = new Server($server);
		$this->assertEquals(array(
			'CONTENT_TYPE' => 'text/html',
			'CONTENT_LENGTH' => '0',
			'ETAG' => 'asdf'
		), $server->getHeaders());
	}

}
