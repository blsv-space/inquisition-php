<?php

namespace Inquisition\Core\Application\Controller;

use Inquisition\Core\Application\Http\Response\ResponseInterface;

/**
 * API Controller Interface
 * Specialized for JSON API responses
 */
interface ApiControllerInterface extends ControllerInterface
{
    /**
     * Return JSON success response
     */
    public function jsonResponse(array $data, int $statusCode = 200): ResponseInterface;

    /**
     * Return JSON error response
     */
    public function jsonErrorResponse(string $message, int $statusCode = 400, array $errors = []): ResponseInterface;

    /**
     * Return paginated JSON response
     */
    public function jsonPaginatedResponse(array $data, int $total, int $page, int $perPage): ResponseInterface;
}
