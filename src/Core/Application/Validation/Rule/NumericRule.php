<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class NumericRule implements RuleInterface
{
    /**
     * @param mixed $value
     * @param array $data
     * @return bool
     */
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true; // Allow null values, use Required rule if needed
        }

        return is_numeric($value);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field must be numeric';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'numeric';
    }
}
