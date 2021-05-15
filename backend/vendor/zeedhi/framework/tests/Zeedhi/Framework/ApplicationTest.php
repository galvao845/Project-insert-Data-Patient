<?php
namespace tests\Zeedhi\Framework;

use tests\Zeedhi\Framework\ApplicationMocks\ErrorController;
use tests\Zeedhi\Framework\ApplicationMocks\ExceptionController;
use tests\Zeedhi\Framework\ApplicationMocks\ControllerImpl;
use tests\Zeedhi\Framework\ApplicationMocks\ExceptionHandlerImpl;
use tests\Zeedhi\Framework\ApplicationMocks\KernelImpl;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\ErrorHandler\ErrorHandlerService;
use Zeedhi\Framework\ErrorHandler\ErrorToException;
use Zeedhi\Framework\Events\PreDispatch;
use Zeedhi\Framework\Events\PostDispatch;
use Zeedhi\Framework\Events\OnException;
use Zeedhi\Framework\Application;
use Zeedhi\Framework\DependencyInjection\InstanceManager;
use Zeedhi\Framework\Routing\Route;
use Zeedhi\Framework\Routing\Router;
use Zeedhi\Framework\ExceptionHandler\ExceptionHandlerService;

class ApplicationTest extends \PHPUnit\Framework\TestCase {

    /** @var Application */
    protected $application;
    /** @var ControllerImpl */
    protected $controller;
    /** @var KernelImpl */
    protected $kernel;
    /** @var ExceptionHandlerService */
    protected $exceptionHandler;

    protected function setUp() {
        $this->controller = new ControllerImpl();

        $instanceManager = InstanceManager::getInstance();
        $instanceManager->registerService('blogController', $this->controller);
        $instanceManager->registerService('exceptionController', new ExceptionController());
        $instanceManager->registerService('errorController', new ErrorController());

        $router = new Router();
        $router->post(new Route(array(Router::METHOD_POST), "/blog", 'blogController', 'listLastPost'));
        $router->post(new Route(array(Router::METHOD_POST), "/exception", 'exceptionController', 'exception'));
        $router->post(new Route(array(Router::METHOD_POST), "/error", 'errorController', 'error'));

        $this->kernel = new KernelImpl();

        $this->exceptionHandler = new ExceptionHandlerService();
        $this->exceptionHandler->addHandler(new ExceptionHandlerImpl());

        $this->application = new Application(
            $instanceManager,
            $this->kernel,
            $router,
            $this->exceptionHandler,
            new PreDispatch\Event(),
            new PostDispatch\Event(),
            new OnException\Event()
        );
    }

    public function test() {
        $this->application->run();
        $this->assertTrue($this->controller->isListLastPostsCalled(), "Method list last post must be called.");
        $this->assertCount(1, $this->kernel->getSentResponses(), "One response must have sent.");
    }

    public function testException() {
        $this->kernel->setRequestPath("/exception");
        $this->application->run();
        /** @var Response $response */
        $response = current($this->kernel->getSentResponses());
        $this->assertEquals("Exception!", $response->getError()->getMessage());
    }

    public function testError() {
        $errorHandlerService = new ErrorHandlerService();
        $errorHandlerService->addHandler(new ErrorToException());
        $this->application->setErrorHandlerService($errorHandlerService);
        $this->kernel->setRequestPath("/error");
        $this->application->run();
        /** @var Response $response */
        $response = current($this->kernel->getSentResponses());
        $this->assertStringStartsWith("PHP Error occurred 'Error controller' ", $response->getError()->getMessage());
        restore_error_handler();
    }
}
