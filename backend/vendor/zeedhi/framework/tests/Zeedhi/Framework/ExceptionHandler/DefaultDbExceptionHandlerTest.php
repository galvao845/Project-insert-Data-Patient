<?php
namespace tests\Zeedhi\Framework\ExceptionHandler;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ExceptionHandler\DefaultDBExceptionHandler;

class DefaultDbExceptionHandlerTest extends \PHPUnit\Framework\TestCase {

    /** @var DefaultDBExceptionHandler */
    protected $exceptionHandler;
    /** @var Response */
    protected $response;

    public function setUp() {
        $this->exceptionHandler = new DefaultDBExceptionHandler();
        $this->response = new Response();
    }

    public function testGetHandledExceptionClasses() {
        $expectedHandledExceptionClasses = array(
            '\Doctrine\DBAL\Driver\DriverException',
            '\Doctrine\DBAL\Exception\DriverException',
            '\Zeedhi\Framework\DataSource\Exception'
        );

        $handledExceptions = $this->exceptionHandler->getHandledExceptionClasses();
        $this->assertCount(3, $handledExceptions);
        foreach($expectedHandledExceptionClasses as $exceptionName) {
            $this->assertContains($exceptionName, $handledExceptions);
        }
    }

    public function testHandleExceptionInDefaultMessages() {
        $exceptionMessage = 'ORA-01400: cannot insert NULL into ("SCHEMA"."TABLE_NAME"."COLUMN_NAME")';
        $exception = new \Zeedhi\Framework\DataSource\Exception($exceptionMessage);
        $this->exceptionHandler->handleException($exception, $this->response);
        $this->assertEmpty($this->response->getDataSets());
        $this->assertEmpty($this->response->getMessages());
        $this->assertEmpty($this->response->getNotifications());
        $responseError = $this->response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $responseError);
        $this->assertEquals("Missing mandatory value.", $responseError->getMessage());
    }

    public function testHandleExceptionNotInDefaultMessages() {
        $exception = new \Zeedhi\Framework\DataSource\Exception("Not in default messages!");
        $this->exceptionHandler->handleException($exception, $this->response);
        $this->assertEmpty($this->response->getDataSets());
        $this->assertEmpty($this->response->getMessages());
        $this->assertEmpty($this->response->getNotifications());
        $responseError = $this->response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $responseError);
        $this->assertEquals($exception->getMessage(), $responseError->getMessage());
    }

    public function testHandleExceptionInAddedMessages() {
        $this->exceptionHandler = new DefaultDBExceptionHandler(array(
            "ORA-00068" => "Number in invalid range."
        ));

        $exceptionMessage = 'ORA-00068: invalid value num for parameter num, must be between num and num';
        $exception = new \Zeedhi\Framework\DataSource\Exception($exceptionMessage);
        $this->exceptionHandler->handleException($exception, $this->response);
        $this->assertEmpty($this->response->getDataSets());
        $this->assertEmpty($this->response->getMessages());
        $this->assertEmpty($this->response->getNotifications());
        $responseError = $this->response->getError();
        $this->assertInstanceOf('\Zeedhi\Framework\DTO\Response\Error', $responseError);
        $this->assertEquals("Number in invalid range.", $responseError->getMessage());
    }
}
