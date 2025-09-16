<?php

namespace Inquisition\Core\Application\Validation\Rule;

use DateTimeImmutable;
use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class DateRule implements RuleInterface
{
    /**
     * @param string $format
     */
    public function __construct(
        private string $format = 'Y-m-d'
    )
    {
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

        $date = DateTimeImmutable::createFromFormat($this->format, $value);
        return $date && $date->format($this->format) === $value;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return sprintf('This field must be a valid date in format %s', $this->format);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date';
    }
}
