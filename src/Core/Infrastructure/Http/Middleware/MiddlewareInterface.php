<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Http\Middleware;

use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;
use Inquisition\Core\Infrastructure\Http\Router\RouteInterface;

/**
 * Middleware Interface
 * Defines the contract for HTTP middleware
 */
interface MiddlewareInterface
{
    /**
     * Process the middleware
     *
     * @param RequestInterface $request The HTTP request
     * @param callable         $next    The next middleware in the stack
     *
     */
    public function process(RequestInterface $request, RouteInterface $route, callable $next): ResponseInterface;
}
