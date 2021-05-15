<?php
namespace tests\Zeedhi\Framework\Parsers;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Routing\Parsers\OldJSONFormat;
use Zeedhi\Framework\Routing\Router;

class OldJSONFormatTest extends \PHPUnit\Framework\TestCase {

	const USER_ID = 'ZhUserTest';
    /** @var Router */
    protected $router;
    /** @var OldJSONFormat */
    protected $routerParser;

    protected function setUp() {
        $this->router = new Router();
        $this->routerParser = new OldJSONFormat(array(__DIR__."/oldRoutes.json", __DIR__."/oldRoutes2.json"));
        $this->router->setParser($this->routerParser);
        $this->router->readRoutes();
    }

    public function test() {
        $this->assertRoute('/container/list', '\Controller\Container', 'getAll');
    }

    protected function assertRoute($uri, $expectedController, $expectedControllerName) {
        list($controllerName, $controllerMethod) = $this->router->resolveRoute(new Request(Router::METHOD_POST, $uri, self::USER_ID));
        $this->assertEquals($expectedController, $controllerName, "The controller must be the expected");
        $this->assertEquals($expectedControllerName, $controllerMethod, "The controller name must be the expected");
    }

    public function testLoadMultipleFileRoutes() {
        $this->assertRoute('/container/list', '\Controller\Container', 'getAll');
        $this->assertRoute('/loginv2', '\Controller\Login', 'loginv2');
    }
}
