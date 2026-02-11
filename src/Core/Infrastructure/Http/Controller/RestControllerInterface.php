<?php

declare(strict_types=1);

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
    public const string ACTION_SHOW = 'show';
    public const string ACTION_STORE = 'store';
    public const string ACTION_UPDATE = 'update';
    public const string ACTION_DESTROY = 'destroy';

    /**
     * GET /resource - List all resources
     *
     * @param array<string, string> $parameters
     */
    public function index(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * GET /resource/{id} - Show a specific resource
     *
     */
    public function show(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * POST /resource - Create a new resource
     *
     */
    public function store(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * PUT/PATCH /resource/{id} - Update existing resource
     *
     */
    public function update(RequestInterface $request, array $parameters): ResponseInterface;

    /**
     * DELETE /resource/{id} - Delete resource
     *
     */
    public function destroy(RequestInterface $request, array $parameters): ResponseInterface;
}
