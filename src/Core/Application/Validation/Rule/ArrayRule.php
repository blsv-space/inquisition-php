<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class ArrayRule implements RuleInterface
{
    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true;
        }

        return is_array($value);
    }

    #[\Override]
    public function message(): string
    {
        return 'This field must be an array';
    }

    #[\Override]
    public function getName(): string
    {
        return 'array';
    }
}
