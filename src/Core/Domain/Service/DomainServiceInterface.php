<?php

namespace Inquisition\Core\Domain\Service;

use Inquisition\Foundation\Singleton\SingletonInterface;

/**
 * Domain Service Interface
 * Marker interface for domain services that contain business logic
 * that doesn't naturally fit within an entity or value object
 */
interface DomainServiceInterface extends SingletonInterface
{
}
