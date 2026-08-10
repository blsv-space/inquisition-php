<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Exception;

use Exception;
use Inquisition\Core\Infrastructure\Http\Router\Exception\ExceptionWithErrors;

class ValidationException extends Exception implements ExceptionWithErrors {

    public function __construct(
        string $message = "",
        private readonly array $errors = [],
    )
    {
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
