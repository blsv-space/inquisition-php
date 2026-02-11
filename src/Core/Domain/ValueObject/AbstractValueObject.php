<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\ValueObject;

use Inquisition\Core\Domain\Validator\StubValueObjectValidator;
use Inquisition\Core\Domain\Validator\ValueObjectValidatorInterface;
use JsonException;

abstract class AbstractValueObject implements ValueObjectInterface
{
    public protected(set) mixed $value;

    protected function __construct(mixed $data)
    {
        $this->value = $data;
    }

    /**
     *
     * @throws JsonException
     */
    #[\Override]
    public function equals(ValueObjectInterface $other): bool
    {
        return $this::class === $other::class
            && $this->__toString() === $other->__toString();

    }

    /**
     *
     * @throws JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->toRaw(), JSON_THROW_ON_ERROR);
    }

    #[\Override]
    abstract public function toRaw(): mixed;

    #[\Override]
    abstract public static function fromRaw(mixed $data): static;

    #[\Override]
    abstract public static function validate(mixed $data): void;

    /**
     * Optional: Get the validator instance for this Value Object
     */
    protected static function getValidator(): ValueObjectValidatorInterface
    {
        return new StubValueObjectValidator();
    }
}
