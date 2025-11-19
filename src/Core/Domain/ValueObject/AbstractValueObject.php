<?php

namespace Inquisition\Core\Domain\ValueObject;

use Inquisition\Core\Domain\Validator\StubValueObjectValidator;
use Inquisition\Core\Domain\Validator\ValueObjectValidatorInterface;
use JsonException;

abstract class AbstractValueObject implements ValueObjectInterface
{
    protected(set) mixed $value;

    protected function __construct(mixed $data)
    {
        $this->value = $data;
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function equals(ValueObjectInterface $other): bool
    {
        return $this::class === $other::class
            && $this->__toString() === $other->__toString();

    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->toRaw(), JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     */
    abstract public function toRaw(): mixed;

    /**
     * @inheritDoc
     */
    abstract public static function fromRaw(mixed $data): static;

    /**
     * @inheritDoc
     */
    abstract public static function validate(mixed $data): void;

    /**
     * Optional: Get the validator instance for this Value Object
     */
    protected static function getValidator(): ValueObjectValidatorInterface
    {
        return new StubValueObjectValidator();
    }
}