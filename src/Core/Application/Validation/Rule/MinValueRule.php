<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class MinValueRule implements RuleInterface
{
    public function __construct(
        private int|float $minValue,
    ) {}

    #[\Override]
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

    #[\Override]
    public function message(): string
    {
        return sprintf('This field must be at least %s', $this->minValue);
    }

    #[\Override]
    public function getName(): string
    {
        return 'min_value';
    }
}
