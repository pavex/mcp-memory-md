<?php

declare(strict_types=1);

namespace MCP\Logger;

/**
 * A no-op logger used as the default when no logging is needed.
 * Implements the Null Object pattern — callers never need to check
 * whether a logger is present; this class silently absorbs all calls.
 */
final class NullLogger implements LoggerInterface
{
    public function log(string $method, mixed $params = null): void
    {
        // intentionally empty
    }
}
