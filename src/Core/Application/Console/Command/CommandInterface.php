<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Console\Command;

interface CommandInterface
{
    public static function getAlias(): string;

    public static function getArguments(): array;

    public function execute(): void;

    public function getDescription(): string;

    public function getHelp(): string;

    public array $parameters {
        get;
    }

}
