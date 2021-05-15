<?php
namespace tests\Zeedhi\Framework\ExceptionHandler;

use tests\Zeedhi\Framework\ApplicationMocks\ExceptionHandlerImpl;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ExceptionHandler\ExceptionHandlerService;

class ExceptionHandlerServiceTest extends \PHPUnit\Framework\TestCase {

    /** @var ExceptionHandlerService */
    protected $exceptionHandler;
    /** @var Response */
    protected $response;

    public function setUp() {
        $this->exceptionHandler = new ExceptionHandlerService();
        $this->response = new Response();
    }

    public function testHandleException() {
        $exception = new \Exception("Test message.");
        $this->exceptionHandler->handle($exception, $this->response);

        $error = $this->response->getError();
        $this->assertEquals("Exception: Test message.", $error->getMessage());
        $stackTrace = $error->getStackTrace();
        $this->assertInternalType('string', $stackTrace);
        $this->assertEquals($stackTrace, $exception->getTraceAsString());
    }

    public function testSpecifiedHandleException() {
        $exception = new \RuntimeException("Test message.");
        $this->exceptionHandler->addHandler(new RuntimeExceptionHandler());
        $this->exceptionHandler->handle($exception, $this->response);

        $error = $this->response->getError();
        $this->assertEquals("[RuntimeException]: Test message.", $error->getMessage());
        $stackTrace = $error->getStackTrace();
        $this->assertInternalType('string', $stackTrace);
        $this->assertEquals($stackTrace, $exception->getTraceAsString());
    }

    public function testHandleExceptionDefaultWithSpecificHandles() {
        $exception = new \Exception("Test message.");
        $this->exceptionHandler->addHandler(new RuntimeExceptionHandler());
        $this->exceptionHandler->handle($exception, $this->response);

        $error = $this->response->getError();
        $this->assertEquals("Exception: Test message.", $error->getMessage());
        $stackTrace = $error->getStackTrace();
        $this->assertInternalType('string', $stackTrace);
        $this->assertEquals($stackTrace, $exception->getTraceAsString());
    }
}
