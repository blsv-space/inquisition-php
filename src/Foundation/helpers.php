<?php

declare(strict_types=1);

if (!function_exists('dd')) {
    /**
     * Dump the given variables and end the script execution.
     *
     * @param mixed ...$vars
     */
    function dd(...$vars): never
    {
        dump(...$vars);
        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the given variable in a readable format.
     *
     * @param mixed ...$vars
     */
    function dump(...$vars): void
    {
        // Check if running in CLI mode
        $isCli = PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';

        if ($isCli) {
            dumpCli(...$vars);
        } else {
            dumpHttp($vars);
        }
    }
}

if (!function_exists('dumpCli')) {
    /**
     * Dump variable for CLI output.
     *
     * @param mixed ...$vars
     */
    function dumpCli(...$vars): void
    {
        echo "\n" . str_repeat('=', 70) . "\n";
        foreach ($vars as $var) {
            var_export($var);
        }
        echo "\n" . str_repeat('=', 70) . "\n";
    }
}

if (!function_exists('dumpHttp')) {
    /**
     * Dump variable for HTTP/RESTful response.
     *
     * @param mixed $vars
     */
    function dumpHttp($vars): void
    {
        // Set proper content type for JSON API
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        echo json_encode($vars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo "\n";
    }
}
