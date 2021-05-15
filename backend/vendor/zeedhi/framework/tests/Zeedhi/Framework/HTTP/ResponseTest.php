<?php
namespace tests\Zeedhi\Framework\HTTP;

use Zeedhi\Framework\HTTP\Response;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
	public function testCreate()
	{
		$response = new Response('foo', '', Response::HTTP_MOVED_PERMANENTLY);
		$this->assertInstanceOf('Zeedhi\Framework\HTTP\Response', $response, 'It is expected an instance of Response');
		$this->assertEquals('foo', $response->getContent(), 'It is expected a "foo" in response content');
		$this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode(), "It is expected status code 301");
	}

	public function testSendHeaders()
	{
		$response = new Response();
		$response->setStatusCode(RESPONSE::HTTP_OK, 'BATMAN');
		$headers = $response->sendHeaders();
		$this->assertObjHeaders($headers);
	}

	public function testSend()
	{
		$response = new Response();
		$responseSend = $response->send();
		$this->assertObjHeaders($responseSend);
	}

	protected function assertObjHeaders($headers) {
		$this->assertObjectHasAttribute('content', $headers, 'it is expected that the object contains the attribute content');
		$this->assertObjectHasAttribute('contentType', $headers, 'it is expected that the object contains the attribute contentType');
		$this->assertObjectHasAttribute('version', $headers, 'it is expected that the object contains the attribute version');
		$this->assertObjectHasAttribute('statusCode', $headers, 'it is expected that the object contains the attribute statusCode');
		$this->assertObjectHasAttribute('statusText', $headers, 'it is expected that the object contains the attribute statusText');
	}

}