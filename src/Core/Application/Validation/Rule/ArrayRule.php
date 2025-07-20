<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class ArrayRule implements RuleInterface
{
    /**
     * @param mixed $value
     * @param array $data
     * @return bool
     */
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true;
        }

        return is_array($value);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field must be an array';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'array';
    }
}
