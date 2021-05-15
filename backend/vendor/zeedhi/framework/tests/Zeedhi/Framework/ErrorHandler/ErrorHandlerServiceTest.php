<?php
namespace tests\Zeedhi\Framework\ErrorHandler;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ErrorHandler\ErrorHandlerService;

class ErrorHandlerServiceTest extends \PHPUnit\Framework\TestCase {

    /** @var ErrorHandlerService */
    protected $errorHandlerService;
    /** @var Response */
    protected $response;

    protected function setUp() {
        $this->errorHandlerService = new ErrorHandlerService();
        $this->errorHandlerService->addHandler(new ErrorHandlerImpl());
        $this->response = new Response();
        $this->errorHandlerService->setResponse($this->response);
        $this->errorHandlerService->register();
    }

    public function tearDown() {
        restore_error_handler();
    }

    public function testHandle() {
        trigger_error("Triggered error.");
        $this->assertInstanceOf(\Zeedhi\Framework\DTO\Response\Error::class, $this->response->getError());
        $this->assertEquals(E_USER_NOTICE, $this->response->getError()->getErrorCode());
    }

    public function testSuppressedError() {
        @trigger_error("Triggered error.");
        $this->assertNull($this->response->getError());
    }

    public function testShutDown() {
        $this->markTestSkipped("It's no possible to make shutdown in the middle of the test.");
    }

    public function testConfigErrorLevels() {
        $oldDisplayErrors = error_reporting(E_ERROR);

        $errorLevel = (E_ALL | E_USER_ERROR) ^ E_USER_NOTICE;
        $this->errorHandlerService = new ErrorHandlerService($errorLevel);
        $this->errorHandlerService->addHandler(new ErrorHandlerImpl());
        $this->errorHandlerService->setResponse($this->response);
        $this->errorHandlerService->register();

        trigger_error("This user warning must be ignored", E_USER_NOTICE);
        $this->assertNull($this->response->getError());

        trigger_error("This user error must be handled", E_USER_ERROR);
        $this->assertInstanceOf(\Zeedhi\Framework\DTO\Response\Error::class, $this->response->getError());
        $this->assertEquals(E_USER_ERROR, $this->response->getError()->getErrorCode());

        trigger_error("A second user warning should not change to current error on reponse.", E_USER_NOTICE);
        $this->assertInstanceOf(\Zeedhi\Framework\DTO\Response\Error::class, $this->response->getError());
        $this->assertEquals(E_USER_ERROR, $this->response->getError()->getErrorCode());

        restore_error_handler();
        $oldDisplayErrors = error_reporting($oldDisplayErrors);
    }
}