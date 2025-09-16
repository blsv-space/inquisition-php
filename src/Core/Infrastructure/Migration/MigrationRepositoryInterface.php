<?php

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Core\Domain\Repository\RepositoryInterface;

interface MigrationRepositoryInterface extends RepositoryInterface
{
    public function hasRun(MigrationInterface $migration): bool;
    public function markAsRun(MigrationInterface $migration): void;
    public function markAsNotRun(MigrationInterface $migration): void;
    public function getAllExecutedVersions(): array;
    public function createMigrationsTableIfNotExists(): void;
}