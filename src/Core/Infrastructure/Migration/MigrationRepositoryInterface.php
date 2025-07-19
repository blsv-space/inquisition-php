<?php

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Core\Domain\Repository\RepositoryInterface;

interface MigrationRepositoryInterface extends RepositoryInterface
{
    public function hasRun(string $version): bool;
    public function markAsRun(string $version): void;
    public function markAsNotRun(string $version): void;
    public function getAllExecutedVersions(): array;
}