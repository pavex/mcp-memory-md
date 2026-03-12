<?php

declare(strict_types=1);

/**
 * Memory MD Server — Configuration
 *
 * Central place for all runtime constants. Using a final class with
 * class constants instead of procedural define() calls makes the
 * configuration namespaced, auto-completable, and impossible to redefine
 * at runtime.
 */
final class Config
{
    /** Absolute path to the persistent memory file. */
    public const MEMORY_FILE = __DIR__ . '/storage/memory.md';

    /** Absolute path to the default memory template loaded on first run. */
    public const DEFAULT_FILE = __DIR__ . '/storage/default.md';

    /** Absolute path to the request log file. */
    public const LOG_FILE = __DIR__ . '/storage/memory.log';

    /** Maximum log file size in bytes before rotation (32 KB). */
    public const LOG_MAX_BYTES = 32 * 1024;

    /** Suffix appended to the rotated log file. */
    public const LOG_ROTATE_SUFFIX = '.old';

    /** Watch loop poll interval in microseconds (500 ms). */
    public const WATCH_INTERVAL_US = 500_000;

    /** MCP protocol version advertised during the initialize handshake. */
    public const MCP_PROTOCOL_VERSION = '2025-03-26';

    /** Human-readable server name returned in serverInfo. */
    public const MCP_SERVER_NAME = 'memory-md';

    /** Server version returned in serverInfo. */
    public const MCP_SERVER_VERSION = '1.0.0';

    /** Prevent instantiation — this class is a pure constants namespace. */
    private function __construct() {}
}
