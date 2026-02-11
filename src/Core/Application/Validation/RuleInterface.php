<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation;

interface RuleInterface
{
    /**
     * Validate the given data against this rule.
     *
     * @param  mixed $value The value to validate
     * @param  array $data  Additional context data for validation
     * @return bool  True if the value is valid, according to this rule
     */
    public function passes(mixed $value, array $data = []): bool;

    /**
     * Get the validation error message for this rule.
     *
     * @return string The error message when validation fails
     */
    public function message(): string;

    /**
     * Get the name/identifier of this rule.
     *
     * @return string The rule name
     */
    public function getName(): string;
}
