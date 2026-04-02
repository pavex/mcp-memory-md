#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Memory MD Server — Entry Point
 *
 * Bootstraps the server: loads all classes, registers models, and starts
 * listening. All protocol and business logic lives in the respective class
 * files — this file intentionally contains nothing else.
 *
 * Usage:
 *   php mcp.php [prefix_or_path]
 *
 * Arguments:
 *   prefix_or_path  Optional argument specifying the memory file.
 *                   Two modes are supported:
 *
 *                   1. PREFIX (no path separator) — name without slashes.
 *                      The file is resolved inside .storage/ next to mcp.php.
 *                      Allowed characters: letters, digits, hyphen, underscore.
 *                      Examples:
 *                        php mcp.php longterm   → .storage/longterm.md
 *                        php mcp.php shared     → .storage/shared.md
 *                      Defaults to "memory" → .storage/memory.md
 *
 *                   2. PATH (contains / or \) — treated as a direct file path.
 *                      Relative paths are resolved from the current working
 *                      directory (cwd), absolute paths are used as-is.
 *                      Examples:
 *                        php mcp.php /data/project.md   → /data/project.md
 *                        php mcp.php ../notes/todo.md   → ../notes/todo.md
 *                        php mcp.php C:\notes\todo.md   → C:\notes\todo.md
 *
 *                   The log file is always stored in .storage/ next to mcp.php,
 *                   named after the base name of the memory file.
 */

$_raw = $argv[1] ?? 'memory';

if (str_contains((string) $_raw, '/') || str_contains((string) $_raw, '\\')) {
    // PATH mode — resolve relative paths against cwd.
    $_file = (string) $_raw;
    if (!str_starts_with($_file, '/') && !preg_match('/^[a-zA-Z]:[\\/\\\\]/', $_file)) {
        $_file = getcwd() . DIRECTORY_SEPARATOR . $_file;
    }
    $_logName = pathinfo($_file, PATHINFO_FILENAME);
} else {
    // PREFIX mode — store inside .storage/.
    $_prefix = preg_match('/^[a-zA-Z0-9_-]+$/', (string) $_raw) ? (string) $_raw : 'memory';
    $_file   = __DIR__ . '/.storage/' . $_prefix . '.md';
    $_logName = $_prefix;
}

define('MEMORY_FILE',    $_file);
define('MEMORY_LOG',     __DIR__ . '/.storage/' . $_logName . '.log');
define('MEMORY_DEFAULT', __DIR__ . '/.storage/' . ($_prefix ?? null) . '-default.md');

unset($_raw, $_file, $_logName, $_prefix);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Logger/LoggerInterface.php';
require_once __DIR__ . '/Logger/NullLogger.php';
require_once __DIR__ . '/Logger/Logger.php';
require_once __DIR__ . '/Server/ToolRegistryInterface.php';
require_once __DIR__ . '/Server/Server.php';
require_once __DIR__ . '/Memory/Memory.php';
require_once __DIR__ . '/Memory/MemoryToolRegistry.php';

use MCP\Logger\Logger;
use MCP\Memory\Memory;
use MCP\Memory\MemoryToolRegistry;
use MCP\Server\Server;

$server = new Server(logger: new Logger());
$server->registerModel(new Memory(), new MemoryToolRegistry());
$server->listen();
