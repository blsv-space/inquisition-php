<?php

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
 */
abstract class AbstractRepository implements RepositoryInterface
{
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
        if (!static::REPOSITORY_WITHOUT_ENTITY &&
            (!class_exists(static::getEntityClassName())
                || !is_subclass_of(static::getEntityClassName(), EntityInterface::class))
        ) {
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
        if (static::TABLE_NAME_PREFIX) {
            return static::TABLE_NAME_PREFIX . ucfirst(static::TABLE_NAME);
        }

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
     * @param ValueObjectInterface $id
     * @return EntityWithIdInterface|null
     * @throws PersistenceException
     */
    public function findById(ValueObjectInterface $id): ?EntityWithIdInterface
    {
        return $this->findOneBy([
            new QueryCriteria(
                field: 'id',
                value: $id,
            )
        ]);
    }

    /**
     * @return EntityInterface[]
     * @throws PersistenceException
     */
    public function findAll(): array
    {
        return $this->findBy();
    }

    /**
     * @param QueryCriteria[] $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return EntityInterface[]
     * @throws PersistenceException
     */
    public function findBy(
        array  $criteria = [],
        ?array $orderBy = null,
        ?int   $limit = null,
        ?int   $offset = null,
    ): array
    {
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
     * @param array $criteria
     * @return EntityInterface|EntityWithIdInterface|null
     * @throws PersistenceException
     */
    public function findOneBy(array $criteria = []): EntityInterface|EntityWithIdInterface|null
    {
        return $this->findBy($criteria, null, 1)[0] ?? null;
    }

    /**
     * Save entity (insert or update)
     *
     * @param EntityWithIdInterface $entity
     * @return void
     * @throws PersistenceException
     */
    public function save(EntityWithIdInterface $entity): void
    {
        $exists = $entity->getId()
            && $this->count(
                [
                    new QueryCriteria(
                        field: 'id',
                        value: $entity->getId()->toRaw(),
                    )
                ]) !== 0;

        if ($exists) {
            $this->updateById($entity);

            return;
        }

        $this->insert($entity);
    }

    /**
     * Delete entity
     *
     * @param EntityWithIdInterface $entity
     * @return bool
     */
    public function removeById(EntityWithIdInterface $entity): bool
    {
        $stmt = $this->connection->connect()->prepare('
            DELETE FROM `' . static::getTableName() . '`
            WHERE `id` = :id;
        ');
        $execute = $stmt->execute(['id' => $entity->getId()->toRaw()]);

        return $stmt->rowCount() > 0 && $execute;
    }

    /**
     * @param QueryCriteria[] $criteria
     * @return int
     * @throws PersistenceException
     */
    public function removeBy(array $criteria): int
    {
        $whereClause = $this->buildWhereClause($criteria);

        $stmt = $this->connection->connect()->prepare('
            DELETE FROM `' . static::getTableName() . '`
             ' . (!empty($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : '')
        );
        $stmt->execute($whereClause['parameters']);

        return $stmt->rowCount();
    }

    /**
     * Count entities
     *
     * @param QueryCriteria[] $criteria
     * @return int
     * @throws PersistenceException
     */
    public function count(array $criteria = []): int
    {
        $whereClause = $this->buildWhereClause($criteria);

        $stmt = $this->connection->connect()->prepare('
            SELECT COUNT(*) FROM `' . static::getTableName() . '`
            ' . (!empty($whereClause['conditions']) ? 'WHERE ' . $whereClause['conditions'] : '') . '
        ');

        $stmt->execute($whereClause['parameters']);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Check if entity exists
     *
     * @param ValueObjectInterface $id
     * @return bool
     * @throws PersistenceException
     */
    public function exists(ValueObjectInterface $id): bool
    {
        return $this->count([
                new QueryCriteria(
                    field: 'id',
                    value: $id->toRaw())
            ]) === 1;
    }

    /**
     * @param EntityWithIdInterface $entity
     * @return void
     * @throws PersistenceException
     */
    public function updateById(EntityWithIdInterface $entity): void
    {
        $rawData = $this->mapEntityToRow($entity);

        $stmt = $this->connection->connect()->prepare('
                UPDATE ' . static::getTableName() . '
                SET ' . implode(', ', array_map(fn($field) => "`$field` = :$field", array_keys($rawData))) . '
                WHERE id = :id;
            ');

        $stmt->execute($rawData);

        if (!$stmt->rowCount()) {
            throw new PersistenceException("Failed to update");
        }
    }

    /**
     * @param EntityInterface $entity
     * @return void
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
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commit database transaction
     *
     * @return void
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Rollback database transaction
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * Execute operation within transaction
     *
     * @param callable $operation
     * @return mixed
     * @throws Throwable
     */
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
     * @param array $row
     * @return EntityInterface
     */
    abstract protected function mapRowToEntity(array $row): EntityInterface;

    /**
     * Map entity to database row
     *
     * @param EntityInterface $entity
     * @return array
     */
    abstract protected function mapEntityToRow(EntityInterface $entity): array;

    /**
     * Build WHERE clause from criteria
     *
     * @param array $criteria
     *
     * @return array
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
     * Build ORDER BY clause
     *
     * @param array|null $orderBy
     * @return string
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
     *
     * @param int|null $limit
     * @param int|null $offset
     * @return string
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

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return static::DATABASE_NAME;
    }
}
