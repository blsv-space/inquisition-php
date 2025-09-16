<?php

namespace Inquisition\Core\Domain\Entity;

use Inquisition\Core\Domain\ValueObject\AbstractValueObject;
use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;

interface EntityWithIdInterface extends EntityInterface
{
    public ValueObjectInterface $id {
        get;
        set;
    }
}