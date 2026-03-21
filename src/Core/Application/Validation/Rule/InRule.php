<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class InRule implements RuleInterface
{
    public function __construct(
        private array $allowedValues,
        private bool $strict = false,
    ) {}

    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true; // Allow null values, use Required rule if needed
        }

        return in_array($value, $this->allowedValues, $this->strict);
    }

    #[\Override]
    public function message(): string
    {
        $values = implode(', ', array_map('strval', $this->allowedValues));
        return sprintf('This field must be one of: %s', $values);
    }

    #[\Override]
    public function getName(): string
    {
        return 'in';
    }
}
