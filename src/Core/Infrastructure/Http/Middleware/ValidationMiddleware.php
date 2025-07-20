<?php

namespace Inquisition\Core\Infrastructure\Http\Middleware;

use Inquisition\Core\Application\Http\Request\RequestInterface;
use Inquisition\Core\Application\Http\Response\ResponseInterface;
use Inquisition\Core\Application\Validation\Exception\ValidationException;
use Inquisition\Core\Application\Validation\ValidatorInterface;


final readonly class ValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    /**
     * @param RequestInterface $request
     * @param callable $next
     * @return ResponseInterface
     * @throws ValidationException
     */
    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        $this->validator->validate($request);

        return $next($request);
    }

}
