<?php

namespace Inquisition\Core\Domain\Factory;

/**
 * Factory Interface
 * Defines the contract for domain object factories
 */
interface FactoryInterface
{
    /**
     * Create a domain object with the given parameters
     */
    public function create(array $parameters = []): object;

    /**
     * Create multiple domain objects
     */
    public function createMany(array $parametersCollection): array;

    /**
     * Check if the factory can create objects of the given type
     */
    public function canCreate(string $type): bool;

    /**
     * Get the type of objects this factory creates
     */
    public function getCreatedType(): string;

    /**
     * Validate parameters before creation
     */
    public function validateParameters(array $parameters): void;
}
