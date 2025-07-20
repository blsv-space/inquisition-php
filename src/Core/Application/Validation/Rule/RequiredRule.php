<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class RequiredRule implements RuleInterface
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

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'This field is required';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'required';
    }
}
