<?php

namespace Inquisition\Core\Infrastructure\Migration;

interface MigrationInterface
{
    public function up(): void;
    public function down(): void;
    public function getVersion(): string;
    public function getDescription(): string;
}