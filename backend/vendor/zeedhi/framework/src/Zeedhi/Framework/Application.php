<?php
namespace Zeedhi\Framework;

use Zeedhi\Framework\DependencyInjection\InstanceManager;
use Zeedhi\Framework\ErrorHandler\ErrorHandlerService;
use Zeedhi\Framework\ExceptionHandler\ExceptionHandlerService;
use Zeedhi\Framework\Routing\Router;
use Zeedhi\Framework\Events\PreDispatch;
use Zeedhi\Framework\Events\PostDispatch;
use Zeedhi\Framework\Events\OnException;

/**
 * Class Application
 *
 * Class that contains methods for managing an application
 *
 * @package Zeedhi\Framework
 */
class Application
{

    /** @var InstanceManager $instanceManager */
    protected $instanceManager;
    /** @var Kernel $kernel */
    protected $kernel;
    /** @var Router $router */
    protected $router;
    /** @var DTO\Request $request */
    protected $request;
    /** @var DTO\Response $response */
    protected $response;
    /** @var ExceptionHandlerService $exceptionHandler */
    protected $exceptionHandler;
    /** @var PreDispatch\Event */
    protected $preDispatchEvent;
    /** @var PostDispatch\Event */
    protected $postDispatchEvent;
    /** @var OnException\Event */
    protected $onExceptionEvent;
    /** @var ErrorHandlerService */
    protected $errorHandlerService;

    /**
     * Construct...
     *
     * @param InstanceManager $instanceManager DI Container used to retrieve controller instances.
     * @param Kernel $kernel Kernel used to factory Request and send Response.
     * @param Router $router Used to know which controller and method call.
     * @param ExceptionHandlerService $exceptionHandler Used to listening the exceptions of application
     * @param PreDispatch\Event $preDispatchEvent
     * @param PostDispatch\Event $postDispatchEvent
     * @param OnException\Event $onExceptionEvent
     */
    public function __construct(
        InstanceManager $instanceManager,
        Kernel $kernel,
        Router $router,
        ExceptionHandlerService $exceptionHandler,
        PreDispatch\Event $preDispatchEvent = null,
        PostDispatch\Event $postDispatchEvent = null,
        OnException\Event $onExceptionEvent = null
    ) {
        $this->instanceManager = $instanceManager;
        $this->kernel = $kernel;
        $this->router = $router;
        $this->exceptionHandler = $exceptionHandler;
        $this->preDispatchEvent = $preDispatchEvent ?: new PreDispatch\Event();
        $this->postDispatchEvent = $postDispatchEvent ?: new PostDispatch\Event();
        $this->onExceptionEvent = $onExceptionEvent ?: new OnException\Event();
    }

    /**
     * @param ErrorHandlerService $errorHandlerService
     */
    public function setErrorHandlerService($errorHandlerService) {
        $this->errorHandlerService = $errorHandlerService;
        $this->errorHandlerService->register();
    }

    protected function createResponse() {
        $this->response = new DTO\Response();
        if ($this->errorHandlerService !== null) {
            $this->errorHandlerService->setResponse($this->response);
        }
    }

    protected function createRequest() {
        $this->request = $this->kernel->getRequest();
    }

    protected function preDispatch() {
        $this->preDispatchEvent->trigger(array(
            $this->request
        ));
    }

    protected function _dispatch() {
        list($controllerName, $method) = $this->router->resolveRoute($this->request);
        $controller = $this->instanceManager->getService($controllerName);
        call_user_func(array($controller, $method), $this->request, $this->response);
    }

    protected function postDispatch() {
        $this->postDispatchEvent->trigger(array(
            $this->request, $this->response
        ));
    }

    protected function dispatch() {
        $this->preDispatch();
        $this->_dispatch();
        $this->postDispatch();
    }

    /**
     * @param \Exception $e
     */
    protected function onException(\Exception $e) {
        $this->onExceptionEvent->trigger(array($e));
    }

    protected function handleException(\Exception $e) {
        try {
            $this->onException($e);
        } catch (\Exception $exception) {
            $e = new \Exception($exception->getMessage(), $exception->getCode(), $e);
        } finally {
            $this->exceptionHandler->handle($e, $this->response);
        }
    }

    protected function sendResponse() {
        $this->kernel->sendResponse($this->response);
    }

    /**
     * Method that starts the application, here is where it all happens
     *
     * @return void
     */
    public function run() {
        $this->createResponse();
        try {
            $this->createRequest();
            $this->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        $this->sendResponse();
    }
}