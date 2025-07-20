<?php

namespace Inquisition\Core\Application\Validation;

use Inquisition\Core\Application\Validation\Exception\ValidationException;

interface ValidatorInterface
{
    /**
     * Validate the given data or request.
     *
     * @param mixed $data The data to validate (could be RequestInterface, array, etc.)
     * @return bool True if validation passes
     * @throws ValidationException If validation fails
     */
    public function validate(mixed $data): bool;

    public array $errors {
        get;
    }

    /**
     * Add a validation rule to this validator.
     *
     * @param string $field The field name to validate
     * @param RuleInterface $rule The validation rule
     * @return self For method chaining
     */
    public function addRule(string $field, RuleInterface $rule): self;

    /**
     * Add multiple validation rules at once.
     *
     * @param array $rules Array where keys are field names and values are RuleInterface instances
     * @return self For method chaining
     */
    public function addRules(array $rules): self;

    /**
     * Clear all validation errors.
     *
     * @return self For method chaining
     */
    public function clearErrors(): self;

    /**
     * Check if there are any validation errors.
     *
     * @return bool True if there are errors
     */
    public function hasErrors(): bool;
}
