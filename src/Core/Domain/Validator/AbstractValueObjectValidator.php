<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Validator;

abstract class AbstractValueObjectValidator implements ValueObjectValidatorInterface
{
    /** @var ValueObjectError[] $errors */
    public protected(set) array $errors = [] {
        get {
            return $this->errors;
        }
    }

    /**
     * Validate the data and throw an exception if invalid
     *
     * @throws DomainValidationException
     */
    #[\Override]
    final public function validate(mixed $data): void
    {
        $this->errors = [];
        $this->doValidate($data);

        /** @psalm-suppress TypeDoesNotContainType */
        if (!empty($this->errors)) {
            /** @psalm-suppress NoValue */
            throw new DomainValidationException($this->errors);
        }
    }

    /**
     * Check if data is valid without throwing an exception
     */
    #[\Override]
    final public function isValid(mixed $data): bool
    {
        $this->errors = [];
        $this->doValidate($data);

        return empty($this->errors);
    }

    /**
     * Add validation error
     */
    protected function addError(string $message, string $field = ''): void
    {
        $error = new ValueObjectError();
        $error->field = $field;
        $error->message = $message;
        $this->errors = [...$this->errors, $error];
    }

    /**
     * Implement validation rules in child classes
     */
    abstract protected function doValidate(mixed $data): void;
}
