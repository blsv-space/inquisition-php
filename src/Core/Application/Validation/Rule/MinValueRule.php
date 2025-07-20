<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class MinValueRule implements RuleInterface
{
    /**
     * @param int|float $minValue
     */
    public function __construct(
        private int|float $minValue
    ) {}

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

        if (!is_numeric($value)) {
            return false;
        }

        return (float) $value >= $this->minValue;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return sprintf('This field must be at least %s', $this->minValue);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'min_value';
    }
}
