<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use DateTimeImmutable;
use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class AfterDateRule implements RuleInterface
{
    public function __construct(
        private DateTimeImmutable $afterDate,
        private string            $format = 'Y-m-d',
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

        $inputDate = DateTimeImmutable::createFromFormat($this->format, $value);

        if (!$inputDate) {
            return false;
        }

        return $inputDate > $this->afterDate;
    }

    #[\Override]
    public function message(): string
    {
        return sprintf('This field must be a date after %s', $this->afterDate->format($this->format));
    }

    #[\Override]
    public function getName(): string
    {
        return 'after_date';
    }
}
