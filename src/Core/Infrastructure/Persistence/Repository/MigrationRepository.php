<?php

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Infrastructure\Migration\MigrationRepositoryInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use RuntimeException;

final class MigrationRepository extends AbstractRepository
    implements MigrationRepositoryInterface
{
    use SingletonTrait;

    protected const string TABLE_NAME = 'inquisition_migrations';

    private function __construct()
    {
        parent::__construct();
        $this->createMigrationsTableIfNotExists();
    }

    public function hasRun(string $version): bool
    {
        $stmt = $this->connection->connect()->prepare(
            "SELECT COUNT(*) FROM `" . static::getTableName() . "` WHERE `version` = ?"
        );
        $stmt->execute([$version]);

        return $stmt->fetchColumn() > 0;
    }

    public function markAsRun(string $version): void
    {
        $stmt = $this->connection->connect()->prepare(
            "INSERT INTO `" . static::getTableName() . "` (`version`, `executed_at`) VALUES (?, NOW())"
        );
        $stmt->execute([$version]);
    }

    public function markAsNotRun(string $version): void
    {
        $stmt = $this->connection->connect()->prepare(
            "DELETE FROM " . static::getTableName() . " WHERE version = ?"
        );
        $stmt->execute([$version]);
    }

    public function getAllExecutedVersions(): array
    {
        $stmt = $this->connection->connect()->query(
            "SELECT version FROM " . static::getTableName() . " ORDER BY version"
        );

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function createMigrationsTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . static::getTableName() . " (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `version` VARCHAR(255) NOT NULL UNIQUE,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->connection->connect()->exec($sql);
    }

    protected function mapRowToEntity(array $row): EntityInterface
    {
        throw new RuntimeException('Should not be called');
    }

    protected function mapEntityToRow(EntityInterface $entity): array
    {
        throw new RuntimeException('Should not be called');
    }
}