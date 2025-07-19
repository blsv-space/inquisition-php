<?php

namespace Inquisition\Core\Infrastructure\Http\Middleware;

use Inquisition\Core\Application\Http\Request\RequestInterface;
use Inquisition\Core\Application\Http\Response\ResponseInterface;

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
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, callable $next): ResponseInterface;
}
