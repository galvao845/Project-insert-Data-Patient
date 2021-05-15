<?php
namespace tests\Zeedhi\Framework\HTTP\Response;

use Exception;

use Zeedhi\Framework\HTTP\Response\JSON;
use Zeedhi\Framework\HTTP\Response;

class JSONTest extends \PHPUnit\Framework\TestCase {

    public function testConstructorEmptyCreatesJsonObject()
    {
        $response = new JSON();
        $this->assertSame('{}', $response->getContent());
    }

    public function testConstructorWithArrayCreatesJsonArray()
    {
        $response = new JSON(array(0, 1, 2, 3));
        $this->assertSame('[0,1,2,3]', $response->getContent());
    }

    public function testConstructorWithAssocArrayCreatesJsonObject()
    {
        $response = new JSON(array('foo' => 'bar'));
        $this->assertSame('{"foo":"bar"}', $response->getContent());
    }

    public function testConstructorWithSimpleTypes()
    {
        $response = new JSON('foo');
        $this->assertSame('"foo"', $response->getContent());

        $response = new JSON(0);
        $this->assertSame('0', $response->getContent());

        $response = new JSON(0.1);
        $this->assertSame('0.1', $response->getContent());

        $response = new JSON(true);
        $this->assertSame('true', $response->getContent());
    }

    public function testConstructorWithCustomStatus()
    {
        $response = new JSON(array(), 202);
        $this->assertSame(202, $response->getStatusCode());
    }

    public function testCreate()
    {
        $response = new JSON(array('foo' => 'bar'), Response::HTTP_BAD_GATEWAY);
        $this->assertInstanceOf('Zeedhi\Framework\HTTP\Response\JSON', $response);
        $this->assertEquals('{"foo":"bar"}', $response->getContent());
        $this->assertEquals(Response::HTTP_BAD_GATEWAY, $response->getStatusCode());
    }

    public function testInvalidCharSet() {
        $errMessage = "Malformed UTF-8 characters, possibly incorrectly encoded";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cant encode response content: '.$errMessage);
        $response = new JSON(array('test' => file_get_contents(__DIR__.'/non_unicode_content.txt')));
    }
}
