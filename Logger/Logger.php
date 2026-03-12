<?php

declare(strict_types=1);

namespace MCP\Logger;

use Config;

/**
 * Lightweight file logger with automatic log rotation.
 * When the log file exceeds Config::LOG_MAX_BYTES it is renamed to
 * memory.log.old (overwriting any previous backup) and a fresh log file
 * is started. This keeps disk usage bounded at roughly 2 × LOG_MAX_BYTES.
 */
final class Logger implements LoggerInterface
{
    /**
     * Appends a single structured line to the log file.
     *
     * Format: [YYYY-MM-DD HH:MM:SS] method=<method> params=<json|null>
     *
     * @param string $method  The MCP method or tool name being logged.
     * @param mixed  $params  Optional parameters to include (JSON-encoded).
     */
    public function log(string $method, mixed $params = null): void
    {
        $this->rotate();
        $timestamp = date('Y-m-d H:i:s');
        $paramsStr = $params !== null
            ? json_encode($params, JSON_UNESCAPED_UNICODE)
            : 'null';
        $line = "[{$timestamp}] method={$method} params={$paramsStr}" . PHP_EOL;
        file_put_contents(\Config::LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotates the log file if it has reached the configured size limit.
     */
    private function rotate(): void
    {
        if (!file_exists(\Config::LOG_FILE)) {
            return;
        }
        clearstatcache(true, \Config::LOG_FILE);
        if (filesize(\Config::LOG_FILE) >= \Config::LOG_MAX_BYTES) {
            rename(\Config::LOG_FILE, \Config::LOG_FILE . \Config::LOG_ROTATE_SUFFIX);
        }
    }
}
