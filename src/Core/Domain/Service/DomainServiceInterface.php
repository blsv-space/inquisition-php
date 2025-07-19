<?php

namespace Inquisition\Core\Domain\Service;

/**
 * Domain Service Interface
 * Marker interface for domain services that contain business logic
 * that doesn't naturally fit within an entity or value object
 */
interface DomainServiceInterface
{
    /**
     * Get the service name/identifier
     */
    public function getName(): string;

    /**
     * Check if the service can handle the given operation
     */
    public function canHandle(string $operation): bool;
}
