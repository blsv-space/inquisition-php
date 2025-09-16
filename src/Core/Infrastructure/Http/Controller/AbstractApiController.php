<?php

namespace Inquisition\Core\Infrastructure\Http\Controller;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Infrastructure\Http\HttpStatusCode;
use Inquisition\Core\Infrastructure\Http\Response\ResponseFactory;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;
use JsonException;

/**
 * Abstract API Controller
 * Base implementation for API controllers with JSON responses
 */
abstract readonly class AbstractApiController implements ApiControllerInterface
{
    /**
     * Return JSON success response
     *
     * @throws JsonException
     */
    public function jsonResponse(array $data, HttpStatusCode $statusCode = HttpStatusCode::OK): ResponseInterface
    {
        return ResponseFactory::json($data, $statusCode);
    }

    /**
     * @param EntityInterface[]|EntityInterface $data
     * @return array
     */
    public function normalizeData(array | EntityInterface $data): array
    {
        if (is_array($data)) {
            return array_map(fn(EntityInterface $entity) => $entity->getAsArray(), $data);
        }

        return $data->getAsArray();
    }

    /**
     * Return JSON error response
     *
     * @throws JsonException
     */
    public function jsonErrorResponse(
        string $message,
        HttpStatusCode $statusCode = HttpStatusCode::BAD_REQUEST,
        array $errors = []
    ): ResponseInterface {
        return ResponseFactory::error($message, $statusCode, $errors);
    }

    /**
     * Return paginated JSON response
     *
     * @throws JsonException
     */
    public function jsonPaginatedResponse(array $data, int $total, int $page, int $perPage): ResponseInterface
    {
        $response = [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ]
        ];

        return ResponseFactory::json($response);
    }

    /**
     * Return not found JSON response
     *
     * @throws JsonException
     */
    protected function notFound(string $message = 'Resource not found'): ResponseInterface
    {
        return ResponseFactory::notFound($message);
    }

    /**
     * Return unauthorized JSON response
     *
     * @throws JsonException
     */
    protected function unauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return ResponseFactory::unauthorized($message);
    }

    /**
     * Return validation error JSON response
     *
     * @throws JsonException
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): ResponseInterface
    {
        return ResponseFactory::validationError($errors, $message);
    }

    /**
     * Return no content response
     */
    protected function noContent(): ResponseInterface
    {
        return ResponseFactory::noContent();
    }
}
