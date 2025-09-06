<?php

namespace Inquisition\Core\Application\Console\Command;

interface CommandInterface
{
    public static function getAlias(): string;

    public static function getArguments(): array;

    public function execute(): void;

    public function getDescription(): string;

    public function getHelp(): string;

    protected(set) array $parameters
    {
        set;
    }

}