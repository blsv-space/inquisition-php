<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Migration;

use Inquisition\Core\Domain\Entity\EntityInterface;

interface MigrationInterface extends EntityInterface
{
    public function up(): void;
    public function down(): void;
    public function getVersion(): string;
    public function getDescription(): string;
}
