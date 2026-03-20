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
 *   php mcp.php [prefix]
 *
 * Arguments:
 *   prefix  Optional file name prefix for the memory file stored in .storage/.
 *            Defaults to "memory", resulting in memory.md.
 *            Allowed characters: letters, digits, hyphen, underscore.
 *            Examples:
 *              php mcp.php longterm   → .storage/longterm.md
 *              php mcp.php shared     → .storage/shared.md
 */

// Resolve prefix from $argv[1], fallback to 'memory'.
$_raw = $argv[1] ?? 'memory';
$_prefix = preg_match('/^[a-zA-Z0-9_-]+$/', (string) $_raw) ? $_raw : 'memory';
define('MEMORY_PREFIX_RESOLVED', $_prefix);
unset($_raw, $_prefix);

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
