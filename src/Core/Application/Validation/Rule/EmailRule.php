<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class EmailRule implements RuleInterface
{
    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true; // Allow null values, use Required rule if needed
        }

        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    #[\Override]
    public function message(): string
    {
        return 'This field must be a valid email address';
    }

    #[\Override]
    public function getName(): string
    {
        return 'email';
    }
}
