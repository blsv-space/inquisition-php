<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Validator;

interface ValueObjectValidatorInterface
{
    /**
     * @var ValueObjectError[] $errors
     */
    public array $errors {
        get;
    }

    public function validate(mixed $data): void;

    public function isValid(mixed $data): bool;

}
