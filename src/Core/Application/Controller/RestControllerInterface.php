<?php

namespace Inquisition\Core\Application\Controller;

use Inquisition\Core\Application\Http\Request\RequestInterface;
use Inquisition\Core\Application\Http\Response\ResponseInterface;

/**
 * REST Controller Interface
 * Defines RESTful resource operations
 */
interface RestControllerInterface extends ControllerInterface
{
    /**
     * GET /resource - List all resources
     */
    public function index(RequestInterface $request): ResponseInterface;

    /**
     * GET /resource/{id} - Show a specific resource
     */
    public function show(RequestInterface $request, int $id): ResponseInterface;

    /**
     * POST /resource - Create a new resource
     */
    public function store(RequestInterface $request): ResponseInterface;

    /**
     * PUT/PATCH /resource/{id} - Update existing resource
     */
    public function update(RequestInterface $request, int $id): ResponseInterface;

    /**
     * DELETE /resource/{id} - Delete resource
     */
    public function destroy(RequestInterface $request, int $id): ResponseInterface;
}
