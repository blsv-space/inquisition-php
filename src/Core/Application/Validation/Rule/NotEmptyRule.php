<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class NotEmptyRule implements RuleInterface
{
    /**
     * @param mixed $value
     * @param array $data
     * @return bool
     */
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        if (is_countable($value)) {
            return count($value) > 0;
        }

        return !empty($value);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field cannot be empty';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'not_empty';
    }
}
