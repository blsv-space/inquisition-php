<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class NotEmptyRule implements RuleInterface
{
    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        if (is_countable($value)) {
            return count($value) > 0;
        }

        return !empty($value);
    }

    #[\Override]
    public function message(): string
    {
        return 'This field cannot be empty';
    }

    #[\Override]
    public function getName(): string
    {
        return 'not_empty';
    }
}
