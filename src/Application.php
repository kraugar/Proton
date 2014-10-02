<?php
/**
 * The Proton Micro Framework
 *
 * @author  Alex Bilbie <hello@alexbilbie.com>
 * @license MIT
 */
namespace Proton;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Orno\Di\Container;
use Orno\Route\RouteCollection;
use League\Event\Emitter as EventEmitter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proton\Events;

/**
 * Proton Application Class
 */
class Application implements HttpKernelInterface, TerminableInterface, \ArrayAccess
{
    /**
     * @var \Orno\Route\RouteCollection
     */
    protected $router;

    /**
     * @var \League\Event\Emitter
     */
    protected $eventEmitter;

    /**
     * @var \Orno\Di\Container
     */
    protected $container;

    /**
     * New Application
     * @return void
     */
    public function __construct()
    {
        $this->container = new Container;
        $this->router = new RouteCollection($this->container);
        $this->eventEmitter = new EventEmitter;
    }

    /**
     * Returns the DI container
     * @return \Orno\Di\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Return the router
     * @return \Orno\Route\RouteCollection
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Add a GET route
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public function get($route, $action)
    {
        $this->router->addRoute('GET', $route, $action);
    }

    /**
     * Add a POST route
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public function post($route, $action)
    {
        $this->router->addRoute('POST', $route, $action);
    }

    /**
     * Add a PUT route
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public function put($route, $action)
    {
        $this->router->addRoute('PUT', $route, $action);
    }

    /**
     * Add a DELETE route
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public function delete($route, $action)
    {
        $this->router->addRoute('DELETE', $route, $action);
    }

    /**
     * Add a PATCH route
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public function patch($route, $action)
    {
        $this->router->addRoute('PATCH', $route, $action);
    }

    /**
     * Handle the request
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  int $type
     * @param  boolean $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
                (new Events\ResponseBeforeEvent($request, $response))
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

            $this->eventEmitter->emit(
                (new Events\ResponseBeforeEvent($request, $response))
            );

            return $response;
        }
    }

    /**
     * (@inheritdoc)
     */
    public function terminate(Request $request, Response $response)
    {
        $this->eventEmitter->emit(
            (new Events\ResponseAfterEvent($request, $response))
        );
    }

    /**
     * Run the application
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $response->send();

        $this->terminate($request, $response);
    }

    /**
     * Subscribe to an event
     * @param  string $event
     * @param  callable $listener
     * @return void
     */
    public function subscribe($event, $listener)
    {
        $this->eventEmitter->addListener($event, $listener);
    }

    /**
     * Array Access get
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->container->get($key);
    }

    /**
     * Array Access set
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
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->container->offsetUnset($key);
    }

    /**
     * Array Access isset
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->container->isRegistered($key);
    }
}
