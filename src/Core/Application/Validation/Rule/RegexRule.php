<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class RegexRule implements RuleInterface
{
    public function __construct(
        private string $pattern,
        private string $customMessage = '',
    ) {
        if ($this->pattern === '') {
            throw new InvalidArgumentException('Pattern cannot be empty');
        }
    }

    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
    }

    #[\Override]
    public function message(): string
    {
        return $this->customMessage ?: 'This field format is invalid';
    }

    #[\Override]
    public function getName(): string
    {
        return 'regex';
    }
}
