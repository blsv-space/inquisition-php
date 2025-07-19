<?php

namespace Inquisition\Core\Domain\ValueObject;

use JsonException;

abstract class AbstractValueObject implements ValueObjectInterface
{
    protected mixed $value;

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function equals(ValueObjectInterface $other): bool
    {
        return $this::class === $other::class
            && $this->toString() === $other->toString();

    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function toString(): string
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
    abstract public function validate(): void;
}