<?php
namespace tests\Zeedhi\Framework\Controller;

use Zeedhi\Framework\Controller\Exception;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Routing\Router;

class SimpleTest extends \PHPUnit\Framework\TestCase {

    CONST USER_ID = 'bhlb9n2oq8lac3di';

    public function testSuccessMethod() {
        $controller = new SimpleImpl();
        $response = new Response();
        $controller->successMethod(new Request(Router::METHOD_POST, '/anyUri', self::USER_ID), $response);
        foreach ($response->getMessages() as $message) {
            $this->assertEquals("Method success fully called.", $message->getMessage(), "Response must have a message.");
        }
    }

    public function testInvalidMethod() {
        $className = 'tests\Zeedhi\Framework\Controller\SimpleImpl';
        $methodName = 'methodThatDoesNotExist';
        $this->expectException('\Zeedhi\Framework\Controller\Exception', "Controller {$className} doesn't has method {$methodName}.");
        $controller = new SimpleImpl();
        $response = new Response();
        $controller->methodThatDoesNotExist(new Request(Router::METHOD_POST, '/anyUri', self::USER_ID), $response);
    }
}
