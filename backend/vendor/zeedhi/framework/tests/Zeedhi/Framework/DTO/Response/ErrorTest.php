<?php
namespace tests\Zeedhi\Framework\DTO\Response;

use Zeedhi\Framework\DTO\Response\Error;

class ErrorTest extends \PHPUnit\Framework\TestCase
{

    public function testCreateError()
    {
        $error = new Error('Fail on fork me GitHub', 500);
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Error', $error, 'It is expected an instance of Error.');
        $this->assertEquals('Fail on fork me GitHub', $error->getMessage(), 'It is expected an error "Fail on fork me GitHub"');
        $this->assertEquals(500, $error->getErrorCode(), 'It is expected an error code 500');
    }

    public function testGetException()
    {
        $exception1 = new \Exception("Tested", 0);
        $error = new Error('Error', 0, "stack trace as a string", $exception1);
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Error', $error, 'It is expected an instance of Error.');
        $this->assertEquals('Error', $error->getMessage(), 'Error message is the provided in constructor');
        $this->assertEquals("stack trace as a string", $error->getStackTrace(), 'Trace is the provided in constructor.');
        $this->assertEquals("Tested", $error->getException()->getMessage(), 'It is expected exception message to be "Tested"');

    }

    public function testFactoryFromException()
    {
        $exception1 = new \Exception("Tested", 500);
        $error = Error::factoryFromException($exception1, "Welcome!", 3);
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Error', $error, 'It is expected an instance of Error.');
        $this->assertEquals("Welcome!", $error->getMessage(), 'It is expected an error "Welcome!"');
        $this->assertEquals(3, $error->getErrorCode(), 'It is expected an error code 3');
        $this->assertEquals($exception1, $error->getException(), 'It is the expected exception');
    }

    public function testMultipleExceptionResponse()
    {
        $exception1 = new \Exception("Pilot", 500);
        $exception2 = new \Exception("Second Episode", 407, $exception1);
        $error = Error::factoryFromException($exception2, "Welcome!", 3);
        $this->assertInstanceOf('Zeedhi\Framework\DTO\Response\Error', $error, 'It is expected an instance of Error.');
        $this->assertEquals("Pilot", $error->getException()->getPrevious()->getMessage(), 'It is expected an error message "Pilot"');
    }

}
