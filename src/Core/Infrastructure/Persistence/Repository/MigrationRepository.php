<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use DateTime;
use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Infrastructure\Migration\MigrationInterface;
use Inquisition\Core\Infrastructure\Migration\MigrationRepositoryInterface;
use Inquisition\Foundation\Singleton\SingletonTrait;
use PDO;
use RuntimeException;

/**
 * @extends AbstractRepository<MigrationInterface>
 * @implements MigrationRepositoryInterface<MigrationInterface>
 */
final class MigrationRepository extends AbstractRepository implements MigrationRepositoryInterface
{
    use SingletonTrait;

    protected const string TABLE_NAME = 'inquisition_migrations';
    protected const bool REPOSITORY_WITHOUT_ENTITY = true;

    private function __construct()
    {
        parent::__construct();
    }

    #[\Override]
    public function hasRun(MigrationInterface $migration): bool
    {
        $stmt = $this->connection->connect()->prepare(
            "SELECT COUNT(*) FROM `" . self::getTableName() . "` WHERE `migration_class` = :migration_class;",
        );
        $stmt->execute([
            'migration_class' => get_class($migration),
        ]);

        return $stmt->fetchColumn() > 0;
    }

    #[\Override]
    public function markAsRun(MigrationInterface $migration): void
    {
        $dateTime = new DateTime()->format('Y-m-d H:i:s');

        $stmt = $this->connection->connect()->prepare(
            "INSERT INTO `" . self::getTableName() . "`
             (`migration_class`, `version`, `executed_at`)
              VALUES (:migration_class, :version, '$dateTime');",
        );
        $stmt->execute([
            'migration_class' => get_class($migration),
            'version' => $migration->getVersion(),
        ]);
    }

    #[\Override]
    public function markAsNotRun(MigrationInterface $migration): void
    {
        $stmt = $this->connection->connect()->prepare(
            "DELETE FROM " . self::getTableName() . " WHERE `migration_class` = :migration_class;",
        );
        $stmt->execute([
            'migration_class' => get_class($migration),
        ]);
    }

    #[\Override]
    public function getAllExecutedVersions(): array
    {
        $stmt = $this->connection->connect()->query(
            "SELECT `version` FROM " . self::getTableName() . " ORDER BY `version`;",
        );

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    #[\Override]
    public function createMigrationsTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::getTableName() . " (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration_class` VARCHAR(255) NOT NULL UNIQUE,
            `version` VARCHAR(255) NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->connection->connect()->exec($sql);
    }

    #[\Override]
    protected function mapRowToEntity(array $row): EntityInterface
    {
        throw new RuntimeException('Should not be called');
    }

    #[\Override]
    protected function mapEntityToRow(EntityInterface $entity): array
    {
        throw new RuntimeException('Should not be called');
    }
}
