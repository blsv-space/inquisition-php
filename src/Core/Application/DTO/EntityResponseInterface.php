<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\DTO;

use Inquisition\Core\Domain\Entity\EntityInterface;

interface EntityResponseInterface
{
    public static function fromEntity(EntityInterface $entity);
    public function getAsArray(): array;
}
