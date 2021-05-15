<?php
/**
 * Created by PhpStorm.
 * User: pauloneto
 * Date: 17/03/2015
 * Time: 13:44
 */

namespace tests\Zeedhi\Framework\ErrorHandler;

use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ErrorHandler\ErrorHandlerService;
use Zeedhi\Framework\ErrorHandler\ErrorToException;
use Zeedhi\Framework\ErrorHandler\ErrorException;

class ErrorToExceptionTest extends \PHPUnit\Framework\TestCase {

    /** @var ErrorHandlerService */
    protected $errorHandlerService;
    /** @var Response */
    protected $response;

    protected function setUp() {
        $this->errorHandlerService = new ErrorHandlerService();
        $this->errorHandlerService->addHandler(new ErrorToException());
        $this->response = new Response();
        $this->errorHandlerService->setResponse($this->response);
        $this->errorHandlerService->register();
    }

    public function testHandle() {
        $this->expectException(ErrorException::class);
        trigger_error("Triggered error.");
    }

    public function tearDown() {
        restore_error_handler();
    }
}
