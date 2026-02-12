<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Core\Domain\Repository\RepositoryInterface;

/**
 * @template TEntity of MigrationInterface
 * @extends RepositoryInterface<TEntity>
 */
interface MigrationRepositoryInterface extends RepositoryInterface
{
    public function hasRun(MigrationInterface $migration): bool;
    public function markAsRun(MigrationInterface $migration): void;
    public function markAsNotRun(MigrationInterface $migration): void;
    public function getAllExecutedVersions(): array;
    public function createMigrationsTableIfNotExists(): void;
}
