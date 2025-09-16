<?php

namespace Inquisition\Core\Infrastructure\Http\Controller;

use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;

/**
 * REST Controller Interface
 * Defines RESTful resource operations
 */
interface RestControllerInterface extends ApiControllerInterface
{
    public const string ACTION_INDEX = 'index';
    public const string ACTION_SHOW  = 'show';
    public const string ACTION_STORE = 'store';
    public const string ACTION_UPDATE = 'update';
    public const string ACTION_DESTROY = 'destroy';

    /**
     * GET /resource - List all resources
     *
     * @param RequestInterface $request
     * @param array<string, string> $parameters
     * @return ResponseInterface
     */
    public function index(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * GET /resource/{id} - Show a specific resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function show(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * POST /resource - Create a new resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function store(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * PUT/PATCH /resource/{id} - Update existing resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function update(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * DELETE /resource/{id} - Delete resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function destroy(RequestInterface $request, array $parameters): ResponseInterface;
}
