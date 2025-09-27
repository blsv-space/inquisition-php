<?php

namespace Inquisition\Core\Infrastructure\Http\Router;

use Inquisition\Core\Infrastructure\Http\Controller\ControllerInterface;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;
use Inquisition\Core\Infrastructure\Http\Router\Exception\RouteNotFoundException;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;

class RequestDispatcher implements SingletonInterface
{
    use SingletonTrait;

    private RouterInterface $router;
    private(set) ?RequestInterface $request = null {
        get {
            return $this->request;
        }
    }

    private function __construct()
    {
        $this->router = Router::getInstance();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws RouteNotFoundException
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $routeMatchResult = $this->router->routeByRequest($request);

        if ($routeMatchResult === null) {
            throw new RouteNotFoundException($request->getMethod()->value, 'No route found for request');
        }

        $route = $routeMatchResult->getRoute();

        $pipeline = $this->buildMiddlewarePipeline(
            $route->middlewares,
            $this->createControllerHandler($route, $routeMatchResult->getParameters())
        );

        return $pipeline($request);
    }

    /**
     * Build a middleware pipeline that chains all middleware together
     *
     * @param array $middlewares
     * @param callable $finalHandler
     * @return callable
     */
    private function buildMiddlewarePipeline(array $middlewares, callable $finalHandler): callable
    {
        $pipeline = $finalHandler;

        foreach (array_reverse($middlewares) as $middleware) {
            $currentPipeline = $pipeline;
            $pipeline = function (RequestInterface $request) use ($middleware, $currentPipeline): ResponseInterface {
                return $middleware->process($request, $currentPipeline);
            };
        }

        return $pipeline;
    }

    /**
     * Create the final handler that calls the controller
     *
     * @param RouteInterface $route
     * @param array $parameters
     * @return callable
     */
    private function createControllerHandler(RouteInterface $route, array $parameters): callable
    {
        return function (RequestInterface $request) use ($route, $parameters): ResponseInterface {
            $controllerClass = $route->controller;
            $actionMethod = $route->action;

            /**
             * @var ControllerInterface $controller
             */
            $controller = new $controllerClass();

            return $controller->{$actionMethod}($request, $parameters);
        };
    }

}