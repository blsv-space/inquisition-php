<?php

namespace Inquisition\Core\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Value Object Interface
 * Defines the contract for all domain value objects
 */
interface ValueObjectInterface
{
    /**
     * Check if this value object equals another value object
     * Value objects are equal if all their attributes are equal
     */
    public function equals(ValueObjectInterface $other): bool;


    /**
     * Get string representation of the value object
     */
    public function __toString(): string;


    /**
     * Get raw representation of the value object
     */
    public function toRaw();

    /**
     * Create value object from raw data
     * @param mixed $data
     * @return static
     * @throws InvalidArgumentException
     */
    public static function fromRaw(mixed $data): static;

    /**
     * Validate the value object data
     * Should throw InvalidArgumentException if invalid
     *
     * @param mixed $data
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validate(mixed $data): void;
}
