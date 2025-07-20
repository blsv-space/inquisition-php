<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class MaxValueRule implements RuleInterface
{
    /**
     * @param int|float $maxValue
     */
    public function __construct(
        private int|float $maxValue
    )
    {
    }

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

        return (float)$value <= $this->maxValue;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return sprintf('This field must be no more than %s', $this->maxValue);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'max_value';
    }
}
