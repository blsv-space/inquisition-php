<?php
namespace Inquisition\Core\Application\Service;

use Inquisition\Core\Application\Command\CommandInterface;
use Inquisition\Core\Application\Query\QueryInterface;

/**
 * Application Service Interface
 * Defines the contract for application services that orchestrate business operations
 * Following CQRS pattern - separates command (write) and query (read) operations
 */
interface ApplicationServiceInterface
{
    /**
     * Execute a command (write operation)
     * Commands modify state and may return results
     *
     * @param CommandInterface $command The command to execute
     * @return mixed The result of the command execution (optional)
     */
    public function executeCommand(CommandInterface $command): mixed;

    /**
     * Execute a query (read operation)
     * Queries retrieve data without modifying state
     *
     * @param QueryInterface $query The query to execute
     * @return mixed The requested data
     */
    public function executeQuery(QueryInterface $query): mixed;

}