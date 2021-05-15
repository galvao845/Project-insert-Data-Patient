<?php
namespace tests\Zeedhi\Framework\Security\OAuth;

class ExceptionTest extends \PHPUnit\Framework\TestCase {


    public function testInvalidFormatToken() {
        $exception = \Zeedhi\Framework\Security\OAuth\Exception::invalidFormatToken();
        $this->assertInstanceOf('\Zeedhi\Framework\Security\OAuth\Exception', $exception);
        $this->assertEquals("An error was encountered while creating token.", $exception->getMessage());
    }

    public function testServiceNotFound() {
        $exception = \Zeedhi\Framework\Security\OAuth\Exception::serviceNotFound("clientId");
        $this->assertInstanceOf('\Zeedhi\Framework\Security\OAuth\Exception', $exception);
        $this->assertEquals("The service with clientID clientId was not found.", $exception->getMessage());
    }

    public function testInvalidToken() {
        $exception = \Zeedhi\Framework\Security\OAuth\Exception::invalidToken();
        $this->assertInstanceOf('\Zeedhi\Framework\Security\OAuth\Exception', $exception);
        $this->assertEquals("The token provided is not valid. Try again or request another token.", $exception->getMessage());
    }
}
