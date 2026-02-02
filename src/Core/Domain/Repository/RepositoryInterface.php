<?php

namespace Inquisition\Core\Domain\Repository;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Domain\Entity\EntityWithIdInterface;
use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnectionInterface;
use Inquisition\Core\Infrastructure\Persistence\Repository\QueryCriteria;
use Inquisition\Foundation\Singleton\SingletonInterface;

/**
 * Domain Repository Interface
 * Defines the contract for domain repositories
 */
interface RepositoryInterface extends SingletonInterface
{

    /**
     * Get the DB table name this repository handles
     * @return string
     */
    public static function getTableName(): string;

    /**
     * Get the DatabaseConnection
     * @return DatabaseConnectionInterface
     */
    public function getConnection(): DatabaseConnectionInterface;

    /**
     * Get the entity class name this repository handles
     */
    public static function getEntityClassName(): string;

    /**
     * Find an entity by its identifier
     */
    public function findById(ValueObjectInterface $id): ?EntityWithIdInterface;

    /**
     * Find all entities
     */
    public function findAll(): array;

    /**
     * Save an entity (insert or update)
     */
    public function save(EntityWithIdInterface $entity): void;

    /**
     * @param EntityWithIdInterface $entity
     *
     * @return bool
     */
    public function removeById(EntityWithIdInterface $entity): bool;

    /**
     * @param QueryCriteria[] $criteria
     * @return int
     */
    public function removeBy(array $criteria): int;

    /**
     * Check if an entity exists by ID
     */
    public function exists(ValueObjectInterface $id): bool;

    /**
     * Count total entities
     */
    public function count(array $criteria = []): int;

    /**
     * Find entities by criteria
     */
    public function findBy(array $criteria = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Find one entity by criteria
     */
    public function findOneBy(array $criteria = []): ?EntityInterface;

    /**
     * @return string
     */
    public function getDatabaseName(): string;

    /**
     * @param callable $operation
     * @return mixed
     */
    public function transactional(callable $operation): mixed;

    /**
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * @return void
     */
    public function commit(): void;

    /**
     * @return void
     */
    public function rollback(): void;
}
