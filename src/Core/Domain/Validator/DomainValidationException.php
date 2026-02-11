<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Validator;

use DomainException;

class DomainValidationException extends DomainException
{
    public function __construct(
        /**
         * @var ValueObjectError[] $errors
         */
        private readonly array $errors,
    ) {
        $message = $this->formatErrors();
        parent::__construct($message);
    }

    /**
     * @return ValueObjectError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function formatErrors(): string
    {
        $messages = array_map(
            fn(ValueObjectError $error) => ($error->field ? "{$error->field}: " : '') . $error->message,
            $this->errors,
        );

        return 'Validation failed: ' . implode(', ', $messages);
    }

}
