<?php

namespace Inquisition\Core\Infrastructure\Http\Response;

use Inquisition\Core\Application\Http\Response\ResponseInterface;
use Inquisition\Core\Infrastructure\Http\HttpStatusCode;
use JsonException;

/**
 * Response Factory
 * Convenient methods for creating different types of responses
 */
class ResponseFactory
{

    /**
     * Create a JSON response
     *
     * @throws JsonException
     */
    public static function json(array $data, HttpStatusCode $statusCode = HttpStatusCode::OK): ResponseInterface
    {
        return new HttpResponse()
            ->setStatusCode($statusCode)
            ->setJsonContent($data);
    }

    /**
     * Create an error response
     *
     * @throws JsonException
     */
    public static function error(
        string         $message,
        HttpStatusCode $statusCode = HttpStatusCode::BAD_REQUEST,
        array          $errors = [],
    ): ResponseInterface {
        $data = ['error' => $message];
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }

        return self::json($data, $statusCode);
    }

    /**
     * Create an HTML response
     */
    public static function html(string $content, HttpStatusCode $statusCode = HttpStatusCode::OK): ResponseInterface
    {
        return new HttpResponse()
            ->setStatusCode($statusCode)
            ->setHtmlContent($content);
    }

    /**
     * Create a redirect response
     */
    public static function redirect(string $url, HttpStatusCode $statusCode = HttpStatusCode::FOUND): ResponseInterface
    {
        return new HttpResponse()->redirect($url, $statusCode);
    }

    /**
     * Create a not-found response
     *
     * @throws JsonException
     */
    public static function notFound(string $message = 'Resource not found'): ResponseInterface
    {
        return self::error($message, HttpStatusCode::NOT_FOUND);
    }

    /**
     * Create an unauthorized response
     *
     * @throws JsonException
     */
    public static function unauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return self::error($message, HttpStatusCode::UNAUTHORIZED);
    }

    /**
     * Create a validation error response
     *
     * @throws JsonException
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): ResponseInterface
    {
        return self::error($message, HttpStatusCode::UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Create a success response with no content
     */
    public static function noContent(): ResponseInterface
    {
        return new HttpResponse()->setStatusCode(HttpStatusCode::NO_CONTENT);
    }
}
