<?php

namespace Inquisition\Core\Domain\Exception;

use Throwable;

/**
 * Domain Exception Interface
 * Marker interface for all domain-related exceptions
 */
interface DomainExceptionInterface extends Throwable
{
    /**
     * Get the domain error code
     */
    public function getDomainErrorCode(): string;

    /**
     * Get additional context data
     */
    public function getContext(): array;

    /**
     * Check if this is a business rule violation
     */
    public function isBusinessRuleViolation(): bool;
}
