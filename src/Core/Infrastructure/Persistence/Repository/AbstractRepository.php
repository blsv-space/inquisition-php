<?php

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Domain\Repository\RepositoryInterface;
use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnectionInterface;
use Inquisition\Core\Infrastructure\Persistence\DatabaseConnections;
use Inquisition\Core\Infrastructure\Persistence\Exception\PersistenceException;
use PDO;
use Throwable;

/**
 * Abstract Repository
 * Base repository class that provides common database operations
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected const string DATABASE_NAME     = 'default';
    protected const string ENTITY_CLASS_NAME = '';
    protected const string TABLE_NAME        = '';

    public readonly DatabaseConnectionInterface $connection;

    /**
     * @throws PersistenceException
     */
    protected function __construct()
    {
        if (!class_exists(static::getEntityClassName()) || !is_subclass_of(static::getEntityClassName(),
                EntityInterface::class)) {
            throw new PersistenceException("Entity class does not exist or is not a subclass of EntityInterface");
        }

        $this->connection = DatabaseConnections::getInstance()->connect(static::DATABASE_NAME);
    }

    /**
     * Get the table name for this repository
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return static::TABLE_NAME;
    }

    /**
     * Get the entity class name
     *
     * @return string
     */
    public static function getEntityClassName(): string
    {
        return static::ENTITY_CLASS_NAME;
    }

    /**
     * @return DatabaseConnectionInterface
     */
    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Find entity by ID
     */
    public function findById(ValueObjectInterface $id): ?EntityInterface
    {
        return $this->findOneBy(['id' => $id->toRaw()]);
    }

    /**
     * Find all entities
     *
     * @return EntityInterface[]
     */
    public function findAll(): array
    {
        return $this->findBy();
    }

    /**
     * Find entities by criteria
     *
     * @return EntityInterface[]
     */
    public function findBy(
        array  $criteria = [],
        ?array $orderBy = null,
        ?int   $limit = null,
        ?int   $offset = null,
    ): array {
        $whereClause = $this->buildWhereClause($criteria);
        $orderByClause = $this->buildOrderByClause($orderBy);
        $limitClause = $this->buildLimitClause($limit, $offset);

        $stmt = $this->connection->connect()->prepare('
            SELECT * FROM ' . static::getTableName() . '
            ' . (count($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : '') . '
            ' . $orderByClause . '
            ' . $limitClause . ';
        ');

        /**
         * @var EntityInterface[] $entities
         */
        $entities = [];

        $stmt->execute($whereClause['parameters']);

        while ($rawData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entities = $this->mapRowToEntity($rawData);
        }

        return $entities;
    }

    /**
     * Find one entity by criteria
     */
    public function findOneBy(array $criteria = []): ?EntityInterface
    {
        return $this->findBy($criteria, null, 1)[0] ?? null;
    }

    /**
     * Save entity (insert or update)
     *
     * @throws Throwable
     */
    public function save(EntityInterface $entity): void
    {
        $rawData = $this->mapEntityToRow($entity);
        $exists = $this->count(['id' => $entity->id->toRaw()]) !== 0;

        if ($exists) {
            $stmt = $this->connection->connect()->prepare('
                UPDATE ' . static::getTableName() . '
                SET ' . implode(', ', array_map(fn($field) => "`$field` = :$field", array_keys($rawData))) . '
                WHERE id = :id;
            ');
            $stmt->execute($rawData);

            return;
        }

        $stmt = $this->connection->connect()->prepare('
                INSERT INTO `' . static::getTableName() . '` 
                (`' . implode('`, `', array_keys($rawData)) . '`)
                VALUES (' . implode(', ', array_fill(0, count($rawData), '?')) . ');
            ');
        $stmt->execute(array_values($rawData));
    }

    /**
     * Delete entity
     */
    public function remove(EntityInterface $entity): bool
    {
        return $this->removeById($entity->id);
    }

    /**
     * Delete entity by ID
     */
    public function removeById(ValueObjectInterface $id): bool
    {
        $stmt = $this->connection->connect()->prepare('
            DELETE FROM `' . static::getTableName() . '`
            WHERE `id` = :id;
        ');
        $execute = $stmt->execute(['id' => $id->toRaw()]);

        return $stmt->rowCount() > 0 && $execute;
    }

    /**
     * Count entities
     */
    public function count(array $criteria = []): int
    {
        $whereClause = $this->buildWhereClause($criteria);

        $stmt = $this->connection->connect()->prepare('
            SELECT COUNT(*) FROM `' . static::getTableName() . '`
            ' . (count($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : '') . '
        ');

        $stmt->execute($whereClause['parameters']);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if entity exists
     */
    public function exists(ValueObjectInterface $id): bool
    {
        return $this->count(['id' => $id->toRaw()]) === 1;
    }

    /**
     * Begin database transaction
     */
    protected function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commit database transaction
     */
    protected function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Rollback database transaction
     */
    protected function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * Execute operation within transaction
     *
     * @throws Throwable
     */
    protected function transactional(callable $operation): mixed
    {
        $this->beginTransaction();

        try {
            $result = $operation();
            $this->commit();

            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Map database row to entity
     */
    abstract protected function mapRowToEntity(array $row): EntityInterface;

    /**
     * Map entity to database row
     */
    abstract protected function mapEntityToRow(EntityInterface $entity): array;

    /**
     * Build WHERE clause from criteria
     */
    protected function buildWhereClause(array $criteria): array
    {
        $conditions = [];
        $parameters = [];

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $placeholders = str_repeat('?,', count($value) - 1) . '?';
                $conditions[] = "`$field` IN ({$placeholders})";
                $parameters = array_merge($parameters, $value);
            } else {
                $conditions[] = "`$field` = ?";
                $parameters[] = $value;
            }
        }

        return [
            'conditions' => implode(' AND ', $conditions),
            'parameters' => $parameters,
        ];
    }

    /**
     * Build ORDER BY clause
     */
    protected function buildOrderByClause(?array $orderBy): string
    {
        if (empty($orderBy)) {
            return '';
        }

        $clauses = [];
        foreach ($orderBy as $field => $direction) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $clauses[] = "$field $direction";
        }

        return 'ORDER BY ' . implode(', ', $clauses);
    }

    /**
     * Build LIMIT and OFFSET clause
     */
    protected function buildLimitClause(?int $limit, ?int $offset): string
    {
        $clause = '';

        if ($limit !== null) {
            $clause .= " LIMIT $limit";
        }

        if ($offset !== null) {
            $clause .= " OFFSET $offset";
        }

        return $clause;
    }
}
