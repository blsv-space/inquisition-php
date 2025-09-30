<?php

namespace Inquisition\Core\Domain\Entity;

use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use Inquisition\Core\Infrastructure\Support\StringHelper;

abstract class BaseEntity implements EntityInterface
{
    public function getAsArray(): array
    {
        $array = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof ValueObjectInterface === false) {
                continue;
            }
            $array[StringHelper::camelCaseToSnakeCase($key)] = $value->toRaw();
        }

        return $array;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return static::class;
    }

    /**
     * @param EntityInterface $other
     * @return bool
     */
    public function equals(EntityInterface $other): bool
    {
        return json_encode($this->getAsArray()) === json_encode($other->getAsArray());
    }

}
