<?php
namespace tests\Zeedhi\Framework\HTTP;

use UnexpectedValueException;

use Zeedhi\Framework\HTTP\Request;
use Zeedhi\Framework\HTTP\Parameter;

class RequestTest extends \PHPUnit\Framework\TestCase {

	public function testCreateRequest() {
		$request = Request::create('http://pokemon:pokemon@example.net:9090/?foo=bar', 'GET');
		$this->assertInstanceOf('Zeedhi\Framework\HTTP\Request', $request, 'Its is expected an instance of Request');
		$this->assertEquals('/?foo=bar', $request->getRequestUri(), 'It is expected a request URI : /?foo=bar');
		$this->assertEquals(9090, $request->getPort());
		$this->assertEquals('example.net', $request->getHost());
		$this->assertFalse($request->isSecure());
		$this->differentUris();
	}

	protected function differentUris() {
		$request = Request::create('https://[::1]/foo?bar=baz');
		$this->assertEquals('/foo?bar=baz', $request->getRequestUri(), 'It is expected a request URI : /foo?bar=baz');
		$this->assertEquals(443, $request->getPort());
		$this->assertEquals('[::1]', $request->getHttpHost());
		$this->assertTrue($request->isSecure());
	}

	public function testCreateFromGlobalsWithRawBody() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['HTTP_USER_ID'] = 'bhlb9n2oq8lac3di';
		$_SERVER['CONTENT_TYPE'] = 'application/json';

		$request = Request::initFromGlobals();
		$this->assertInstanceOf('Zeedhi\Framework\HTTP\Request', $request, 'Its is expected an instance of Request');
		$this->assertEquals('POST', $request->getMethod(), 'It is expected a method POST');
		$this->assertEquals('', $request->getContent(), 'It is expected an content is empty');
		$this->assertEquals('bhlb9n2oq8lac3di', $request->getUserId(), 'It is expected userId equals "bhlb9n2oq8lac3di"');
		unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_USER_ID'], $_SERVER['CONTENT_TYPE']);
	}

	public function testCreateFromGlobals() {
		$_GET['foo1'] = 'bar1';
		$_POST['requestType'] = 'filter';
		$_COOKIE['foo3'] = 'bar3';
		$_FILES['foo4'] = array('bar4');
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['HTTP_USER_ID'] = 'bhlb9n2oq8lac3di';

		$request = Request::initFromGlobals();
		$this->assertInstanceOf('Zeedhi\Framework\HTTP\Request', $request, 'Its is expected an instance of Request');
		$this->assertEquals('POST', $request->getMethod(), 'It is expected a method POST');
		$this->assertEquals('', $request->getContent(), 'It is expected an content is empty');
		$this->assertEquals('filter', $request->getRequestType(), 'It is expected a requestType: filter');
		$this->assertEquals('bhlb9n2oq8lac3di', $request->getUserId(), 'It is expected userId equals "bhlb9n2oq8lac3di"');
		unset($_GET['foo1'], $_POST['requestType'], $_COOKIE['foo3'], $_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_USER_ID']);
	}

	public function testRequestWithRawBody() {
		$json = '{"jsonrpc":"2.0","method":"echo","id":7,"params":["Hello World"]}';
		$request = Request::create('http://cdn.zeedhi.com/jsonrpc', 'POST', array(), array(), $json);
		$this->assertEquals($json, $request->getContent());
		$this->assertEquals(80, $request->getPort());
		$this->assertEquals('cdn.zeedhi.com', $request->getHttpHost());
		$this->assertFalse($request->isSecure());
	}

	public function testGetContentReturnsResource() {
		$req = new Request();
		$retVal = $req->getContent(true);
		$this->assertInternalType('resource', $retVal);
		$this->assertEquals("", fread($retVal, 1));
		$this->assertTrue(feof($retVal));
	}

	/**
	 * @dataProvider provideHostsToValidate
	 */
	public function testHostValidate($host, $isValid, $expectedHost = null, $expectedPort = null) {
		$request = Request::create('/');
		$request->getHeaders()->set('host', $host);
		if ($isValid) {
			$this->assertSame($expectedHost ?: $host, $request->getHost());
			if ($expectedPort) {
				$this->assertSame($expectedPort, $request->getPort());
			}
		} else {
			$this->expectException(UnexpectedValueException::class);
			$this->expectExceptionMessage('Invalid Host');
			$request->getHost();
		}
	}

	public function testGetQueryParameters() {
		$parameters = array(
			'foo' => 'bar',
			'baz' => 'qux'
		);
		$request = new Request($parameters);

		$this->assertEquals(new Parameter($parameters), $request->getQueryParameters());
		$this->assertEquals(new Parameter(array()), $request->getRequestParameters());
	}

	public function testGetRequestParameters() {
		$parameters = array(
			'foo' => 'bar',
			'baz' => 'qux'
		);
		$request = new Request(array(), $parameters);

		$this->assertEquals(new Parameter(array()), $request->getQueryParameters());
		$this->assertEquals(new Parameter($parameters), $request->getRequestParameters());
	}

	public function provideHostsToValidate() {
		return array(
			array('.a', false),
			array('a..', false),
			array('a.', true),
			array("\xE9", false),
			array('[::1]', true),
			array('[::1]:80', true, '[::1]', 80),
			array(str_repeat('.', 101), false),
		);
	}

}
