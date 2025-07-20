<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class ConfirmedRule implements RuleInterface
{
    /**
     * @param string $confirmationField
     */
    public function __construct(
        private string $confirmationField
    )
    {
        if ($this->confirmationField === '') {
            throw new InvalidArgumentException('Confirmation field cannot be empty');
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

        if (array_key_exists($this->confirmationField, $data) === false) {
            return false;
        }

        $confirmationValue = $data[$this->confirmationField] ?? null;
        return $value === $confirmationValue;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'The confirmation does not match';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'confirmed';
    }
}
