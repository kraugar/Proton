<?php

namespace Proton;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Orno\Di\Container;
use Orno\Route\RouteCollection;
use League\Event\Emitter as EventEmitter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proton\Events;

class Application implements HttpKernelInterface, TerminableInterface, \ArrayAccess
{
    protected $router;

    protected $eventEmitter;

    protected $container;

    public function __construct()
    {
        $this->container = new Container;
        $this->router = new RouteCollection($this->container);
        $this->eventEmitter = new EventEmitter;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function get($route, $action)
    {
        $this->router->addRoute('GET', $route, $action);
    }

    public function post($route, $action)
    {
        $this->router->addRoute('POST', $route, $action);
    }

    public function put($route, $action)
    {
        $this->router->addRoute('PUT', $route, $action);
    }

    public function delete($route, $action)
    {
        $this->router->addRoute('DELETE', $route, $action);
    }

    public function patch($route, $action)
    {
        $this->router->addRoute('PATCH', $route, $action);
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->eventEmitter->emit(
            (new Events\RequestReceivedEvent($request))
        );

        try {

            $dispatcher = $this->router->getDispatcher();
            $response = $dispatcher->dispatch(
                $request->getMethod(),
                $request->getPathInfo()
            );

            $this->eventEmitter->emit(
                (new Events\ResponseBeforeEvent($request))
            );

            return $response;

        } catch (\Exception $e) {

            if (!$catch) {
                throw $e;
            }

            $response = new Response;
            $response->setStatusCode(500);
            $response->setContent(json_encode([
                'error' =>  [
                    'message'   =>  $e->getMessage(),
                    'trace'     =>  $e->getTrace()
                ]
            ]));

            return $response;
        }
    }

    public function terminate(Request $request, Response $response)
    {
        $this->eventEmitter->emit(
            (new Events\ResponseAfterEvent($request))
        );
    }

    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $response->send();

        $this->terminate($request, $response);
    }

    public function subscribe($event, $listener)
    {
        $this->eventEmitter->addListener($event, $listener);
    }

    /**
     * Array Access get
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->container->get($key);
    }

    /**
     * Array Access set
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->container->singleton($key, $value);
    }

    /**
     * Array Access unset
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->container->items[$key]);
        unset($this->container->singletons[$key]);
    }

    /**
     * Array Access isset
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->container->isRegistered($key);
    }
}
