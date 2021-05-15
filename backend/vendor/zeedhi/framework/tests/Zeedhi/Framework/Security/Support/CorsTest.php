<?php
namespace tests\Zeedhi\Framework\Security\Support;


use Zeedhi\Framework\HTTP\Request;
use Zeedhi\Framework\HTTP\Response;
use Zeedhi\Framework\Security\Support\Cors;
use Zeedhi\Framework\Security\Support\CorsOptions;

class CorsTest extends \PHPUnit\Framework\TestCase {

	private function buildCorsService($allowedOrigins, $allowedHeaders, $allowedMethods,
									  $maxAge, $exposedHeaders, $supportCredentials) {
		$corsOptions = new CorsOptions(
			$allowedOrigins,
			$allowedHeaders,
			$allowedMethods,
			$maxAge,
			$exposedHeaders,
			$supportCredentials
		);
		return new Cors($corsOptions);
	}

	public function testCanDetectCorsRequest() {
		$request = Request::create('http://localhost', 'OPTIONS');
		$corsService = $this->buildCorsService('*', '*', '*', 10, false, false);
		$this->assertFalse($corsService->isPreflightRequest($request));
		$request->getHeaders()->set('Origin', 'http://example.com');
		$request->getHeaders()->set('Access-Control-Request-Method', 'POST');
		$this->assertEquals(true, $corsService->isPreflightRequest($request));
	}


	public function testPreflightCorsRequest() {
		$request = Request::create('http://localhost', 'OPTIONS');
		$corsService = $this->buildCorsService('*', '*', '*', 10, array('x-exposed-header', 'x-another-exposed-header'), true);
		$request->getHeaders()->set('Origin', 'http://example.com');
		$request->getHeaders()->set('Access-Control-Request-Method', 'POST');
		$request->getHeaders()->set('Access-Control-Request-Headers', 'FOO, BAR');
		$response = $corsService->handlePreflightRequest($request);
		$responseHeaders = $response->getHeaders();
		$this->assertTrue($responseHeaders->has('access-control-allow-origin'));
		$this->assertEquals('http://example.com', $responseHeaders->get('access-control-allow-origin'));
		$this->assertTrue($responseHeaders->has('access-control-max-age'));
		$this->assertEquals(10, $responseHeaders->get('access-control-max-age'));
		$this->assertTrue($responseHeaders->has('access-control-allow-methods'));
		$this->assertEquals('POST', $responseHeaders->get('access-control-allow-methods'));
		$this->assertTrue($responseHeaders->has('access-control-allow-headers'));
		$this->assertEquals('FOO, BAR', $responseHeaders->get('access-control-allow-headers'));
		$this->assertTrue($responseHeaders->has('access-control-expose-headers'));
		$this->assertEquals('x-exposed-header, x-another-exposed-header', $responseHeaders->get('access-control-expose-headers'));
		$this->assertTrue($responseHeaders->has('access-control-allow-credentials'));
		$this->assertEquals('true', $responseHeaders->get('access-control-allow-credentials'));
	}

	public function testPreflightCorsOriginNotAllowed() {
		$request = Request::create('http://localhost', 'OPTIONS');
		$corsService = $this->buildCorsService(array('http://www.google.com'), '*', '*', 10, false, false);
		$this->assertFalse($corsService->isPreflightRequest($request));
		$request->getHeaders()->set('Origin', 'http://example.com');
		$request->getHeaders()->set('Access-Control-Request-Method', 'POST');
		$response = $corsService->handlePreflightRequest($request);
		$this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
	}

	public function testPreflightCorsMethodNotAllowed() {
		$request = Request::create('http://localhost', 'OPTIONS');
		$corsService = $this->buildCorsService(array('http://example.com'), '*', array('GET'), 10, false, false);
		$this->assertFalse($corsService->isPreflightRequest($request));
		$request->getHeaders()->set('Origin', 'http://example.com');
		$request->getHeaders()->set('Access-Control-Request-Method', 'POST');
		$response = $corsService->handlePreflightRequest($request);
		$this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
	}

	public function testPreflightCorsHeadersNotAllowed() {
		$request = Request::create('http://localhost', 'OPTIONS');
		$corsService = $this->buildCorsService(array('http://example.com'), array('FOO'), array('POST'), 10, false, false);
		$this->assertFalse($corsService->isPreflightRequest($request));
		$request->getHeaders()->set('Origin', 'http://example.com');
		$request->getHeaders()->set('Access-Control-Request-Method', 'POST');
		$request->getHeaders()->set('Access-Control-Request-Headers', 'FOO, BAR');
		$response = $corsService->handlePreflightRequest($request);
		$this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
	}


}
