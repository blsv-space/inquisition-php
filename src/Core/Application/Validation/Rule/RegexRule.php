<?php

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class RegexRule implements RuleInterface
{
    /**
     * @param string $pattern
     * @param string $customMessage
     */
    public function __construct(
        private string $pattern,
        private string $customMessage = ''
    )
    {
        if ($this->pattern === '') {
            throw new InvalidArgumentException('Pattern cannot be empty');
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

        if (!is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->customMessage ?: 'This field format is invalid';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'regex';
    }
}
