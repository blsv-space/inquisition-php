<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Entity;

use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;

abstract class BaseEntity implements EntityInterface
{
    #[\Override]
    public function getAsArray(): array
    {
        $array = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof ValueObjectInterface === false) {
                continue;
            }
            $array[$key] = $value->toRaw();
        }

        return $array;
    }

    #[\Override]
    public function getEntityType(): string
    {
        return static::class;
    }

    #[\Override]
    public function equals(EntityInterface $other): bool
    {
        return json_encode($this->getAsArray()) === json_encode($other->getAsArray());
    }

}
