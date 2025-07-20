<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class BooleanRule implements RuleInterface
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

        return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field must be true or false';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'boolean';
    }
}
