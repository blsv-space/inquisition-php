<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class NumericRule implements RuleInterface
{
    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true; // Allow null values, use Required rule if needed
        }

        return is_numeric($value);
    }

    #[\Override]
    public function message(): string
    {
        return 'This field must be numeric';
    }

    #[\Override]
    public function getName(): string
    {
        return 'numeric';
    }
}
