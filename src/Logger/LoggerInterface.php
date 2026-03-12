<?php

declare(strict_types=1);

namespace MCP\Logger;

interface LoggerInterface
{
    /**
     * Logs a single MCP method call with optional parameters.
     *
     * @param string $method  The MCP method or tool name being logged.
     * @param mixed  $params  Optional parameters to include (JSON-encoded).
     */
    public function log(string $method, mixed $params = null): void;
}
