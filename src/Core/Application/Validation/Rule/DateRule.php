<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use DateTimeImmutable;
use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class DateRule implements RuleInterface
{
    public function __construct(
        private string $format = 'Y-m-d',
    ) {}

    #[\Override]
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

    #[\Override]
    public function message(): string
    {
        return sprintf('This field must be a valid date in format %s', $this->format);
    }

    #[\Override]
    public function getName(): string
    {
        return 'date';
    }
}
