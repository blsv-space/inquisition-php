<?php

namespace Inquisition\Core\Domain\Entity;

use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use InvalidArgumentException;

abstract class BaseEntity implements EntityInterface
{
    public ValueObjectInterface $id {
        get {
            return $this->id;
        }
        set {
            if (!$value instanceof ValueObjectInterface) {
                throw new InvalidArgumentException('ID must be a ValueObjectInterface');
            }
            $this->id = $value;
        }
    }

    abstract public function getAsArray(): array;

    public function equals(EntityInterface $other): bool
    {
        return $this->getEntityType() === $other->getEntityType()
            && $this->id === $other->id;
    }

    public function getEntityType(): string
    {
        return static::class;
    }

}
