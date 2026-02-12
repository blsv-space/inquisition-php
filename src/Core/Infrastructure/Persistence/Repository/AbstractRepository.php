<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use Inquisition\Core\Domain\Entity\EntityInterface;
use Inquisition\Core\Domain\Entity\EntityWithIdInterface;
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
 *
 * @template TEntity of EntityInterface
 * @implements  RepositoryInterface<TEntity>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    public const string ORDER_ASC = 'ASC';
    public const string ORDER_DESC = 'DESC';
    protected const string DATABASE_NAME = 'default';
    protected const string ENTITY_CLASS_NAME = '';
    protected const bool REPOSITORY_WITHOUT_ENTITY = false;
    protected const string TABLE_NAME_PREFIX = '';
    protected const string TABLE_NAME = '';

    public readonly DatabaseConnectionInterface $connection;

    /**
     * @throws PersistenceException
     */
    protected function __construct()
    {
        if (!static::REPOSITORY_WITHOUT_ENTITY
            && (!class_exists(static::getEntityClassName())
                || !is_subclass_of(static::getEntityClassName(), EntityInterface::class))
        ) {
            throw new PersistenceException("Entity class does not exist or is not a subclass of EntityInterface");
        }

        $this->connection = DatabaseConnections::getInstance()->connect(static::DATABASE_NAME);
    }

    /**
     * Get the table name for this repository
     *
     */
    #[\Override]
    public static function getTableName(): string
    {
        if (static::TABLE_NAME_PREFIX) {
            return static::TABLE_NAME_PREFIX . ucfirst(static::TABLE_NAME);
        }

        return static::TABLE_NAME;
    }

    /**
     * Get the entity class name
     *
     * @return class-string<TEntity>
     */
    #[\Override]
    public static function getEntityClassName(): string
    {
        return static::ENTITY_CLASS_NAME;
    }

    #[\Override]
    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @throws PersistenceException
     */
    #[\Override]
    public function findById(ValueObjectInterface $id): ?EntityWithIdInterface
    {
        return $this->findOneBy([
            new QueryCriteria(
                field: 'id',
                value: $id,
            ),
        ]);
    }

    /**
     * @throws PersistenceException
     * @return EntityInterface[]
     */
    #[\Override]
    public function findAll(): array
    {
        return $this->findBy();
    }

    /**
     * @param  QueryCriteria[]      $criteria
     * @throws PersistenceException
     * @psalm-return list<TEntity>
     */
    #[\Override]
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
            ' . (!empty($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : '') . '
            ' . $orderByClause . '
            ' . $limitClause . ';
        ');

        /**
         * @var EntityInterface[] $entities
         */
        $entities = [];

        $stmt->execute($whereClause['parameters']);

        while ($rawData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $entities[] = $this->mapRowToEntity($rawData);
        }

        return $entities;
    }

    /**
     * Find one entity by criteria
     *
     * @throws PersistenceException
     * @psalm-return TEntity|null
     */
    #[\Override]
    public function findOneBy(array $criteria = []): ?EntityInterface
    {
        return $this->findBy($criteria, null, 1)[0] ?? null;
    }

    /**
     * Save entity (insert or update)
     *
     * @psalm-param TEntity $entity
     *
     * @throws PersistenceException
     */
    #[\Override]
    public function save(EntityInterface $entity): void
    {
        if (!is_subclass_of($entity, EntityWithIdInterface::class)) {
            if (is_null($entity->getId())) {
                throw new PersistenceException('Entity that implements EntityWithIdInterface should have an ID');
            }
            $exists = $entity->getId()
                && $this->count(
                    [
                        new QueryCriteria(
                            field: 'id',
                            value: $entity->getId()->toRaw(),
                        ),
                    ],
                ) !== 0;

            if ($exists) {
                $this->updateById($entity);

                return;
            }
        }

        $this->insert($entity);
    }

    /**
     * Delete entity
     *
     * @psalm-param TEntity $entity
     * @throws PersistenceException
     */
    #[\Override]
    public function removeById(EntityInterface $entity): bool
    {
        if (is_subclass_of($entity, EntityWithIdInterface::class) && !$entity->getId()) {
            throw new PersistenceException('Entity must implement EntityWithIdInterface and have an ID');
        }

        $stmt = $this->connection->connect()->prepare('
            DELETE FROM `' . static::getTableName() . '`
            WHERE `id` = :id;
        ');
        $execute = $stmt->execute(['id' => $entity->getId()->toRaw()]);

        return $stmt->rowCount() > 0 && $execute;
    }

    /**
     * @param  QueryCriteria[]      $criteria
     * @throws PersistenceException
     */
    #[\Override]
    public function removeBy(array $criteria): int
    {
        $whereClause = $this->buildWhereClause($criteria);

        $stmt = $this->connection->connect()->prepare(
            '
            DELETE FROM `' . static::getTableName() . '`
             ' . (!empty($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : ''),
        );
        $stmt->execute($whereClause['parameters']);

        return $stmt->rowCount();
    }

    /**
     * Count entities
     *
     * @param  QueryCriteria[]      $criteria
     * @throws PersistenceException
     */
    #[\Override]
    public function count(array $criteria = []): int
    {
        $whereClause = $this->buildWhereClause($criteria);

        $stmt = $this->connection->connect()->prepare('
            SELECT COUNT(*) FROM `' . static::getTableName() . '`
            ' . (!empty($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : '') . '
        ');

        $stmt->execute($whereClause['parameters']);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if entity exists
     *
     * @throws PersistenceException
     */
    #[\Override]
    public function exists(ValueObjectInterface $id): bool
    {
        return $this->count([
            new QueryCriteria(
                field: 'id',
                value: $id->toRaw(),
            ),
        ]) === 1;
    }

    /**
     * @psalm-param TEntity $entity
     *
     * @throws PersistenceException
     */
    public function updateById(EntityInterface $entity): void
    {
        if (!is_subclass_of($entity, EntityWithIdInterface::class) || !$entity->getId()) {
            throw new PersistenceException('Entity must implement EntityWithIdInterface');
        }

        $rawData = $this->mapEntityToRow($entity);
        $fields = array_filter(array_keys($rawData), fn($k) => $k !== 'id');

        $stmt = $this->connection->connect()->prepare('
                UPDATE ' . static::getTableName() . '
                SET ' . implode(', ', array_map(fn($field) => "`$field` = :$field", $fields)) . '
                WHERE id = :id;
            ');

        $stmt->execute($rawData);

        if (!$stmt->rowCount()) {
            throw new PersistenceException("Failed to update");
        }
    }

    /**
     * @psalm-param TEntity $entity
     *
     * @throws PersistenceException
     */
    public function insert(EntityInterface $entity): void
    {
        $rawData = $this->mapEntityToRow($entity);

        $stmt = $this->connection->connect()->prepare('
                INSERT INTO `' . static::getTableName() . '` 
                (`' . implode('`, `', array_keys($rawData)) . '`)
                VALUES (' . implode(', ', array_fill(0, count($rawData), '?')) . ');
            ');
        $stmt->execute(array_values($rawData));

        if (!$stmt->rowCount()) {
            throw new PersistenceException("Failed to insert");
        }
    }

    /**
     * Begin database transaction
     *
     */
    #[\Override]
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commit database transaction
     *
     */
    #[\Override]
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Rollback database transaction
     *
     */
    #[\Override]
    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * Execute operation within transaction
     *
     * @throws Throwable
     */
    #[\Override]
    public function transactional(callable $operation): mixed
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
     *
     * @psalm-return TEntity
     */
    abstract protected function mapRowToEntity(array $row): EntityInterface;

    /**
     * Map entity to database row
     *
     * @psalm-param TEntity $entity
     */
    abstract protected function mapEntityToRow(EntityInterface $entity): array;

    /**
     * Build WHERE clause from criteria
     *
     *
     * @throws PersistenceException
     */
    protected function buildWhereClause(array $criteria): array
    {
        $conditions = [];
        $parameters = [];

        foreach ($criteria as $criterion) {
            if (!$criterion instanceof QueryCriteria) {
                throw new PersistenceException('Criteria must implement QueryCriteria');
            }

            $conditions[] = $criterion->compile();
            $parameters = array_merge($parameters, $criterion->getParameters());
        }

        return [
            'conditions' => implode(' AND ', $conditions),
            'parameters' => $parameters,
        ];
    }

    /**
     * @throws PersistenceException
     */
    protected function buildOrderByClause(?array $orderBy): string
    {
        if (empty($orderBy)) {
            return '';
        }

        $clauses = [];
        foreach ($orderBy as $field => $direction) {
            if (!is_string($field) || !is_string($direction)
                || !in_array(strtoupper($direction), [self::ORDER_ASC, self::ORDER_DESC])
                || empty($field)
            ) {
                throw new PersistenceException('Order by must be an array of Record<string, ASC|DESC>');
            }

            $direction = strtoupper($direction) === self::ORDER_DESC ? self::ORDER_DESC : self::ORDER_ASC;
            $clauses[] = "$field $direction";
        }

        return 'ORDER BY ' . implode(', ', $clauses);
    }

    /**
     * Build LIMIT and OFFSET clause
     *
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

    #[\Override]
    public function getDatabaseName(): string
    {
        return static::DATABASE_NAME;
    }
}
