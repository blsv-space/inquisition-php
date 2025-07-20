<?php

namespace Inquisition\Core\Application\Validation\Rule;

use DateTimeImmutable;
use Inquisition\Core\Application\Validation\RuleInterface;

final readonly class AfterDateRule implements RuleInterface
{
    /**
     * @param DateTimeImmutable $afterDate
     * @param string $format
     */
    public function __construct(
        private DateTimeImmutable $afterDate,
        private string            $format = 'Y-m-d'
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

        $inputDate = DateTimeImmutable::createFromFormat($this->format, $value);

        if (!$inputDate) {
            return false;
        }

        return $inputDate > $this->afterDate;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return sprintf('This field must be a date after %s', $this->afterDate->format($this->format));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'after_date';
    }
}
