<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class MaxLengthRule implements RuleInterface
{
    public function __construct(
        private int $maxLength,
    ) {
        if ($this->maxLength < 1) {
            throw new InvalidArgumentException('Maximum length must be at least 1');
        }
    }

    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true; // Allow null values, use Required rule if needed
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $this->maxLength;
        }

        if (is_array($value) || is_countable($value)) {
            return count($value) <= $this->maxLength;
        }

        if (is_numeric($value)) {
            return strlen((string) $value) <= $this->maxLength;
        }

        return false;
    }

    #[\Override]
    public function message(): string
    {
        return sprintf('This field cannot be longer than %d characters', $this->maxLength);
    }

    #[\Override]
    public function getName(): string
    {
        return 'max_length';
    }
}
