<?php

namespace Inquisition\Core\Application\Command;

interface CommandHandlerInterface
{

    /**
     * Handle the command and perform the business operation
     * @param CommandInterface $command
     * @return mixed Result of the operation (optional)
     */
    public function handle(CommandInterface $command): mixed;
}