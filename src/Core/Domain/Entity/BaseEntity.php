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

    public function getAsArray(): array
    {
        $vars = get_object_vars($this);

        return array_map(function ($var) {
            if (!$var instanceof ValueObjectInterface) {
                return null;
            }

            return $var->toRaw();
        }, $vars);
    }

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
