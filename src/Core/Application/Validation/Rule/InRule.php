<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class InRule implements RuleInterface
{
    /**
     * @param array $allowedValues
     * @param bool $strict
     */
    public function __construct(
        private array $allowedValues,
        private bool $strict = false
    ) {}

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

        return in_array($value, $this->allowedValues, $this->strict);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        $values = implode(', ', array_map('strval', $this->allowedValues));
        return sprintf('This field must be one of: %s', $values);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'in';
    }
}
