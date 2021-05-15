<?php
namespace tests\Zeedhi\Framework;

use Zeedhi\Framework\DataSource\FilterCriteria;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Routing\Route;
use Zeedhi\Framework\Routing\Router;

class RouteTest extends \PHPUnit\Framework\TestCase {

    /** @var  \Zeedhi\Framework\Routing\Route */
    protected $route;

    /** @var  String */
    protected $uri;

    /** @var  String */
    protected $methods;

    /** var String */
    protected $controller;

    /** var String */
    protected $controllerMethod;

    public function setUp(){
        $this->methods = array(Router::METHOD_GET);
        $this->uri = '/home/blog';
        $this->controller = 'blogController';
        $this->controllerMethod = 'getLastPosts';
        $this->route = new Route($this->methods, $this->uri, $this->controller, $this->controllerMethod, Request::TYPE_FILTER);
    }

    public function testGetters() {
        $this->route->getUri();
        $this->assertEquals($this->uri, $this->route->getUri(), "Uri must be the expected.");
        $this->assertContains(Router::METHOD_GET, $this->route->getMethods(), "Get must be in methods.");
    }

    public function testMatch (){
        $request = new Request('GET', '/home/blog', uniqid());
        $this->assertTrue($this->route->match($request), 'The match must be true if the uri is the same');
    }

    public function testSupport(){
        $testMethod = 'GET';
        $this->assertTrue($this->route->support($testMethod), 'The support must be true if the method given is \'GET\'');
    }

    public function testMatchRequest() {
        $filterRequest = new Request\Filter(new FilterCriteria('posts'), 'GET', '/home/blog', uniqid());
        $this->assertTrue($this->route->matchRequest($filterRequest));
    }

    public function testMatchInvalidRequest() {
        $filterRequest = new Request\Row(array(), 'GET', '/home/blog', uniqid());
        $this->assertFalse($this->route->matchRequest($filterRequest));
    }

    public function testRouteWithParameters() {
        $route = new Route(
            array('POST'),
            '/home/blog/{blogName}',
            'blogController',
            'getLastPostForBlog',
            Request::TYPE_EMPTY,
            array(array('name' => 'blogName', 'regex' => '[A-Za-z0-9]*'))
        );
        $this->assertEquals(array(array('name' => 'blogName', 'regex' => '[A-Za-z0-9]*')), $route->getParameters());
        $request = new Request('POST', '/home/blog/zeedhi', uniqid());
        $this->assertTrue($route->matchRequest($request));
    }
}

