<?php
/**
 * Created by PhpStorm.
 * User: icaroharry
 * Date: 29/07/14
 * Time: 14:16
 */

namespace tests\Zeedhi\Framework\Parsers;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Routing\Exception as RoutingException;
use Zeedhi\Framework\Routing\Parsers\JSONFormat;
use Zeedhi\Framework\Routing\Parsers\JSONFormatException;
use Zeedhi\Framework\Routing\Router;

class JSONFormatTest extends \PHPUnit\Framework\TestCase {

	const USER_ID = 'ZhUserTest';
    /** @var Router */
    protected $router;
    /** @var JSONFormat */
    protected $routerParser;

    protected function setUp() {
        $this->routerParser = new JSONFormat(array(__DIR__."/routes.json", __DIR__."/routes2.json"));
        $this->router = new Router();
        $this->router->setParser($this->routerParser);
        $this->router->readRoutes();
    }

    public function test() {
        $this->assertRoute('/blog', 'blogController', 'lastTenPosts');
    }

    public function testParametrizedRoute() {
        $this->assertRoute('/blog/author/777', 'blogController', 'findAuthor');
    }

    public function testInvalidParametrizedRoute() {
        $this->expectException(RoutingException::class);
        $this->expectExceptionMessage("Route /blog/author/sixsixsix does not exist.");
        $this->router->resolveRoute(new Request(Router::METHOD_POST, '/blog/author/sixsixsix', self::USER_ID));
    }

    protected function assertRoute($uri, $expectedController, $expectedControllerName) {
        list($controllerName, $controllerMethod) = $this->router->resolveRoute(new Request(Router::METHOD_POST, $uri, self::USER_ID));
        $this->assertEquals($expectedController, $controllerName, "The controller must be the expected");
        $this->assertEquals($expectedControllerName, $controllerMethod, "The controller name must be the expected");
    }

    public function testLoadMultipleFileRoutes() {
        $this->assertRoute('/blog', 'blogController', 'lastTenPosts');
        $this->assertRoute('/account', 'accountController', 'find');
    }

    public function testJSONParseError() {
        $this->expectException(JSONFormatException::class);
        $this->expectExceptionMessage('Invalid JSON on router file "'.__DIR__.'/routes_invalid.json": Syntax error');

        $routerParser = new JSONFormat(array(__DIR__."/routes_invalid.json"));

        $router = new Router();
        $router->setParser($routerParser);
        $router->readRoutes();
    }
}
