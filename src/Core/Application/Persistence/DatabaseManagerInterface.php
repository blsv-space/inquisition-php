<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Persistence;

interface DatabaseManagerInterface
{
    public function create(): void;
    public function reset(): void;
    public function exists(): bool;
}
