<?php

namespace Inquisition\Core\Infrastructure\Http\Controller;

use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;

/**
 * Base Controller Interface
 * Defines the contract for all controllers in the application
 */
interface ControllerInterface
{
    /**
     * Handle an HTTP request and return a response
     *
     * @param RequestInterface $request    The incoming HTTP request
     * @param array            $parameters Route parameters (optional)
     *
     * @return ResponseInterface The HTTP response
     */
    public function handle(RequestInterface $request, array $parameters = []): ResponseInterface;
}
