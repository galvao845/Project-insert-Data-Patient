<?php
namespace tests\Zeedhi\Framework;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Routing\Exception as RoutingException;
use Zeedhi\Framework\Routing\Router;
use Zeedhi\Framework\Routing\Route;

class RouterTest extends \PHPUnit\Framework\TestCase {

	const USER_ID = 'ZhUserTest';

    /** @var \Zeedhi\Framework\Routing\Router */
    protected $router;

    protected $uri;
    protected $controller;
    protected $controllerMethod;

    public function setUp() {
        $this->router = new Router();
        $this->uri = '/home/blog';
        $this->controller = 'blogController';
        $this->controllerMethod = 'getLastPosts';
    }

    public function testPostRoute() {
        $expectedController = 'blogController';
        $expectedControllerMethod = 'getLastPosts';
        $postRoute = new Route(array(Router::METHOD_POST), $this->uri, $this->controller, $this->controllerMethod);
        $this->router->post($postRoute);
        $resolvedRoute = $this->router->resolveRoute(new Request(Router::METHOD_POST, $this->uri, self::USER_ID));
        $this->assertEquals($expectedController, $resolvedRoute[0], 'If the method given is POST, the first position of the retrieved array must be the controller name');
        $this->assertEquals($expectedControllerMethod, $resolvedRoute[1], 'If the method given is POST, the second position of the retrieved array must be the controllerMethod name');
    }

    public function testGetRoute() {
        $expectedController = 'blogController';
        $expectedControllerMethod = 'getLastPosts';
        $getRoute = new Route(array(Router::METHOD_GET), $this->uri, $this->controller, $this->controllerMethod);
        $this->router->get($getRoute);
        $resolvedRoute = $this->router->resolveRoute(new Request(Router::METHOD_GET, $this->uri, self::USER_ID));
        $this->assertEquals($expectedController, $resolvedRoute[0], 'If the method given is GET, the first position of the retrieved array must be the controller name');
        $this->assertEquals($expectedControllerMethod, $resolvedRoute[1], 'If the method given is GET, the second position of the retrieved array must be the controllerMethod name');
    }

    public function testDeleteRoute() {
        $expectedController = 'blogController';
        $expectedControllerMethod = 'getLastPosts';
        $deleteRoute = new Route(array(Router::METHOD_DELETE), $this->uri, $this->controller, $this->controllerMethod);
        $this->router->delete($deleteRoute);
        $resolvedRoute = $this->router->resolveRoute(new Request(Router::METHOD_DELETE, $this->uri, self::USER_ID));
        $this->assertEquals($expectedController, $resolvedRoute[0], 'If the method given is DELETE, the first position of the retrieved array must be the controller name');
        $this->assertEquals($expectedControllerMethod, $resolvedRoute[1], 'If the method given is DELETE, the second position of the retrieved array must be the controllerMethod name');
    }

    public function testPutRoute() {
        $expectedController = 'blogController';
        $expectedControllerMethod = 'getLastPosts';
        $putRoute = new Route(array(Router::METHOD_PUT), $this->uri, $this->controller, $this->controllerMethod);
        $this->router->put($putRoute);
        $resolvedRoute = $this->router->resolveRoute(new Request(Router::METHOD_PUT, $this->uri, self::USER_ID));
        $this->assertEquals($expectedController, $resolvedRoute[0], 'If the method given is PUT, the first position of the retrieved array must be the controller name');
        $this->assertEquals($expectedControllerMethod, $resolvedRoute[1], 'If the method given is PUT, the second position of the retrieved array must be the controllerMethod name');
    }

    public function testInvalidMethod() {
        $this->expectException(RoutingException::class);
        $this->expectExceptionMessage('Invalid method INVALID_METHOD');
        $putRoute = new Route(array(Router::METHOD_POST), $this->uri, $this->controller, $this->controllerMethod);
        $this->router->post($putRoute);
        $this->router->resolveRoute(new Request('INVALID_METHOD', $this->uri, self::USER_ID));
    }

    public function testInvalidUri() {
        $this->expectException(RoutingException::class);
        $this->expectExceptionMessage('Route /invalid/uri does not exist.');
        $postRoute = new Route(array(Router::METHOD_POST), $this->uri, $this->controller, $this->controllerMethod);
        $this->router->post($postRoute);
        $this->router->resolveRoute(new Request(Router::METHOD_POST, '/invalid/uri', self::USER_ID));
    }

    public function testAnyRoute() {
        $expectedController = 'blogController';
        $expectedControllerMethod = 'getLastPosts';
        $methods = array(Router::METHOD_PUT, Router::METHOD_POST, Router::METHOD_GET, Router::METHOD_DELETE);
        $putRoute = new Route($methods, $this->uri, $this->controller, $this->controllerMethod);
        $this->router->any($putRoute);
        foreach ($methods as $method) {
            $resolvedRoute = $this->router->resolveRoute(new Request($method, $this->uri, self::USER_ID));
            $this->assertEquals($expectedController, $resolvedRoute[0], 'The first position of the retrieved array must be the controller name, in any method.');
            $this->assertEquals($expectedControllerMethod, $resolvedRoute[1], 'The second position of the retrieved array must be the controllerMethod name, in any method.');
        }
    }

    public function testMatchRequest() {
        $this->router->get(new Route(array('GET'), '/home/blog', 'blogController', 'getLastPosts', Request::TYPE_FILTER));
        $filterRequest = new Request\Filter(new FilterCriteria('posts'), 'GET', '/home/blog', uniqid());
        $resolvedRoute = $this->router->resolveRoute($filterRequest);
        $this->assertEquals('blogController', $resolvedRoute[0], 'The first position of the retrieved array must be the controller name.');
        $this->assertEquals('getLastPosts', $resolvedRoute[1], 'The second position of the retrieved array must be the controllerMethod name.');
    }

    public function testResolveRouteInvalidRequest() {
        $this->expectException(RoutingException::class);
        $this->expectExceptionMessage("Route /home/blog does not exist.");
        $this->router->get(new Route(array('GET'), '/home/blog', 'blogController', 'getLastPosts', Request::TYPE_FILTER));
        $request = new Request\Row(array(), 'GET', '/home/blog', uniqid());
        $this->router->resolveRoute($request);
    }

    public function testMatchRequestWithMultiplesRoutesOnSamePath() {
        $this->router->post(new Route(array('POST'), '/home/blog/post', 'blogController', 'publishNewPosts', Request::TYPE_DATA_SET));
        $this->router->post(new Route(array('POST'), '/home/blog/post', 'blogController', 'publishNewPost', Request::TYPE_ROW));

        $filterRequest = new Request\Row(array('post' => 'fooBarBaz'), 'POST', '/home/blog/post', uniqid());
        $resolvedRoute = $this->router->resolveRoute($filterRequest);
        $this->assertEquals('blogController', $resolvedRoute[0], 'The first position of the retrieved array must be the controller name.');
        $this->assertEquals('publishNewPost', $resolvedRoute[1], 'The second position of the retrieved array must be the controllerMethod name.');

        $filterRequest = new Request\DataSet(new DataSet('posts', array('post' => 'fooBarBaz')), 'POST', '/home/blog/post', uniqid());
        $resolvedRoute = $this->router->resolveRoute($filterRequest);
        $this->assertEquals('blogController', $resolvedRoute[0], 'The first position of the retrieved array must be the controller name.');
        $this->assertEquals('publishNewPosts', $resolvedRoute[1], 'The second position of the retrieved array must be the controllerMethod name.');
    }

    public function testRouteWithParametersInPath() {
        $route = new Route(array('POST'), '/home/blog/{blogName}', 'blogController', 'getLastPostForBlog', Request::TYPE_EMPTY, array('blogName'));
        $this->router->post($route);

        $request = new Request('POST', '/home/blog/zeedhi', uniqid());
        list($controller, $method) = $this->router->resolveRoute($request);
        $this->assertEquals('blogController', $controller);
        $this->assertEquals('getLastPostForBlog', $method);
        $this->assertEquals('zeedhi', $request->getParameter('blogName'));
    }

    public function testRouteWithParametersInPathWithRegexValidations() {
        $this->router->post(new Route(
            array('POST'),
            '/home/blog/{blogName}',
            'blogController',
            'getLastPostForBlog',
            Request::TYPE_EMPTY,
            array(array('name' => 'blogName', 'regex' => '[A-Za-z0-9]*'))
        ));

        $request = new Request('POST', '/home/blog/zeedhi', uniqid());
        list($controller, $method) = $this->router->resolveRoute($request);
        $this->assertEquals('blogController', $controller);
        $this->assertEquals('getLastPostForBlog', $method);
        $this->assertEquals('zeedhi', $request->getParameter('blogName'));
    }

    public function testInvalidRouteWithParametersInPathWithRegexValidations() {
        $this->expectException(RoutingException::class);
        $this->expectExceptionMessage('Route /home/blog/zeedh1 does not exist.');

        $this->router->post(new Route(
            array('POST'),
            '/home/blog/{blogName}',
            'blogController',
            'getLastPostForBlog',
            Request::TYPE_EMPTY,
            array(array('name' => 'blogName', 'regex' => '[A-Za-z]*'))
        ));

        $request = new Request('POST', '/home/blog/zeedh1', uniqid());
        $this->router->resolveRoute($request);
    }
}