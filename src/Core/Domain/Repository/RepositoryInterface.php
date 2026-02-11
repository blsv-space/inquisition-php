<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Repository;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnectionInterface;
use Inquisition\Core\Infrastructure\Persistence\Repository\QueryCriteria;
use Inquisition\Foundation\Singleton\SingletonInterface;

/**
 * Domain Repository Interface
 * Defines the contract for domain repositories
 *
 * @template TEntity of EntityInterface
 */
interface RepositoryInterface extends SingletonInterface
{
    /**
     * Get the DB table name this repository handles
     */
    public static function getTableName(): string;

    /**
     * Get the DatabaseConnection
     */
    public function getConnection(): DatabaseConnectionInterface;

    /**
     * Get the entity class name this repository handles
     *
     * @psalm-return class-string<TEntity>
     */
    public static function getEntityClassName(): string;

    /**
     * Find an entity by its identifier
     *
     * @psalm-return TEntity|null
     */
    public function findById(ValueObjectInterface $id): ?EntityInterface;

    /**
     * Find all entities
     */
    public function findAll(): array;

    /**
     * Save an entity (insert or update)
     *
     * @psalm-param TEntity $entity
     */
    public function save(EntityInterface $entity): void;

    /**
     * @psalm-param TEntity $entity
     */
    public function removeById(EntityInterface $entity): bool;

    /**
     * @param QueryCriteria[] $criteria
     */
    public function removeBy(array $criteria): int;

    /**
     * Check if an entity exists by ID
     */
    public function exists(ValueObjectInterface $id): bool;

    /**
     * Count total entities
     *
     * @param QueryCriteria[] $criteria
     */
    public function count(array $criteria = []): int;

    /**
     * Find entities by criteria
     *
     * @param QueryCriteria[] $criteria
     *
     * @psalm-return list<TEntity>
     */
    public function findBy(array $criteria = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Find one entity by criteria
     *
     * @param QueryCriteria[] $criteria
     *
     * @psalm-return TEntity|null
     */
    public function findOneBy(array $criteria): ?EntityInterface;

    public function getDatabaseName(): string;

    public function transactional(callable $operation): mixed;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;

    /**
     * @psalm-param TEntity $entity
     */
    public function insert(EntityInterface $entity): void;
}
