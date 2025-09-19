<?php

namespace Inquisition\Core\Infrastructure\Http\Controller;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Infrastructure\Http\HttpStatusCode;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;

/**
 * API Controller Interface
 * Specialized for JSON API responses
 */
interface ApiControllerInterface extends ControllerInterface
{
    /**
     * Return JSON success response
     */
    public function jsonResponse(
        array $data,
        HttpStatusCode $statusCode = HttpStatusCode::OK
    ): ResponseInterface;

    /**
     * Return JSON error response
     */
    public function jsonErrorResponse(
        string $message,
        HttpStatusCode $statusCode = HttpStatusCode::BAD_REQUEST,
        array $errors = []
    ): ResponseInterface;

    /**
     * Return paginated JSON response
     */
    public function jsonPaginatedResponse(
        array $data,
        int $total,
        int $page,
        int $perPage
    ): ResponseInterface;

    /**
     *  Return normalized response data
     *
     * @param array|EntityInterface $data
     * @return array
     */
    public function normalizeResponse(array | EntityInterface $data): array;
}
