<?php
namespace Inquisition\Core\Application\Console\Command;

interface CommandInterface
{
    public function execute(): void;
    public function getDescription(): string;
    public function getHelp(): string;
    public function getAlias(): string;
    public function getArguments(): array;
}