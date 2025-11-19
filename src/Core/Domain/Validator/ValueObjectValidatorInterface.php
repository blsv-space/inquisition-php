<?php

namespace Inquisition\Core\Domain\Validator;

interface ValueObjectValidatorInterface
{
    /**
     * @var ValueObjectError[] $errors
     */
    public array $errors {
        get;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function validate(mixed $data): void;

    /**
     * @param mixed $data
     * @return bool
     */
    public function isValid(mixed $data): bool;

}