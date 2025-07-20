<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class IntegerRule implements RuleInterface
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

        if (is_int($value)) {
            return true;
        }

        if (is_string($value) && ctype_digit(ltrim($value, '-'))) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field must be an integer';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'integer';
    }
}
