<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class MinLengthRule implements RuleInterface
{
    /**
     * @param int $minLength
     */
    public function __construct(
        private int $minLength
    ) {
        if ($this->minLength < 1) {
            throw new InvalidArgumentException('Minimum length must be at least 1');
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
            return true; // Allow null values, use Required rule if needed
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $this->minLength;
        }

        if (is_array($value) || is_countable($value)) {
            return count($value) >= $this->minLength;
        }

        if (is_numeric($value)) {
            return strlen((string) $value) >= $this->minLength;
        }

        return false;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return sprintf('This field must be at least %d characters long', $this->minLength);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'min_length';
    }
}
