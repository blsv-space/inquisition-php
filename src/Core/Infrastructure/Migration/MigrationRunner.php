<?php

namespace Inquisition\Core\Infrastructure\Migration;

use Exception;
use Inquisition\Core\Infrastructure\Persistence\Repository\MigrationRepository;
use Inquisition\Foundation\Singleton\SingletonInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use RuntimeException;

final class MigrationRunner implements SingletonInterface
{
    use SingletonTrait;

    private readonly MigrationRepositoryInterface $migrationRepository;
    /**
     * @var MigrationInterface[]
     */
    private array $migrations = [];

    private function __construct()
    {
        $this->migrationRepository = MigrationRepository::getInstance();
    }

    public function registerMigration(MigrationInterface $migration): void
    {
        $this->migrations[] = $migration;
    }

    /**
     * @param MigrationInterface[]|null $migrations
     */
    public function runUp(?array $migrations = null): void
    {
        $migrations = $migrations ?? $this->migrations;

        $this->sortMigrationsByVersion($migrations);

        foreach ($migrations as $migration) {
            if (!$this->migrationRepository->hasRun($migration->getVersion())) {
                $this->executeMigration($migration, 'up');
                $this->migrationRepository->markAsRun($migration->getVersion());
                echo "✓ Migrated: {$migration->getVersion()} - {$migration->getDescription()}\n";
            } else {
                echo "- Already migrated: {$migration->getVersion()}\n";
            }
        }
    }

    /**
     * @param MigrationInterface[] $migrations
     */
    public function runDown(int $steps = 1, ?array $migrations = null): void
    {
        $migrations = $migrations ?? $this->migrations;
        $this->sortMigrationsByVersion($migrations, false);

        $executedVersions = $this->migrationRepository->getAllExecutedVersions();

        $migrationsToRollback = array_filter($migrations, function ($migration) use ($executedVersions) {
            return in_array($migration->getVersion(), $executedVersions);
        });

        $count = 0;
        foreach ($migrationsToRollback as $migration) {
            if ($count >= $steps) {
                break;
            }

            $this->executeMigration($migration, 'down');
            $this->migrationRepository->markAsNotRun($migration->getVersion());
            echo "✓ Rolled back: {$migration->getVersion()} - {$migration->getDescription()}\n";
            $count++;
        }
    }

    private function executeMigration(MigrationInterface $migration, string $direction): void
    {
        $connection = $this->migrationRepository->getConnection();

        $connection->beginTransaction();
        try {
            if ($direction === 'up') {
                $migration->up();
            } else {
                $migration->down();
            }

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            throw new RuntimeException(
                "Migration failed: {$migration->getVersion()} - {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    private function sortMigrationsByVersion(array &$migrations, bool $ascending = true): void
    {
        usort($migrations, function (MigrationInterface $a, MigrationInterface $b) use ($ascending) {
            $result = version_compare($a->getVersion(), $b->getVersion());

            return $ascending ? $result : -$result;
        });
    }
}