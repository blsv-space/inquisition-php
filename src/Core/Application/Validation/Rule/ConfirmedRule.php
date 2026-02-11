<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class ConfirmedRule implements RuleInterface
{
    public function __construct(
        private string $confirmationField,
    ) {
        if ($this->confirmationField === '') {
            throw new InvalidArgumentException('Confirmation field cannot be empty');
        }
    }

    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (array_key_exists($this->confirmationField, $data) === false) {
            return false;
        }

        $confirmationValue = $data[$this->confirmationField] ?? null;
        return $value === $confirmationValue;
    }

    #[\Override]
    public function message(): string
    {
        return 'The confirmation does not match';
    }

    #[\Override]
    public function getName(): string
    {
        return 'confirmed';
    }
}
