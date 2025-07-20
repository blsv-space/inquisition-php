<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class BetweenRule implements RuleInterface
{
    /**
     * @param int|float $min
     * @param int|float $max
     */
    public function __construct(
        private int|float $min,
        private int|float $max
    )
    {
        if ($this->min > $this->max) {
            throw new InvalidArgumentException('Minimum value must be less than or equal to maximum value');
        }
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

        $numValue = (float)$value;
        return $numValue >= $this->min && $numValue <= $this->max;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return sprintf('This field must be between %s and %s', $this->min, $this->max);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'between';
    }
}
