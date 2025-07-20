<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class StringRule implements RuleInterface
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

        return is_string($value);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field must be a string';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'string';
    }
}
