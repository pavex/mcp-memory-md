#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Memory MD Server — Entry Point
 *
 * Bootstraps the server: loads all classes, registers models, and starts
 * listening. All protocol and business logic lives in the respective class
 * files — this file intentionally contains nothing else.
 */

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/src/Logger/LoggerInterface.php';
require_once __DIR__ . '/src/Logger/NullLogger.php';
require_once __DIR__ . '/src/Logger/Logger.php';
require_once __DIR__ . '/src/Server/ToolRegistryInterface.php';
require_once __DIR__ . '/src/Server/Server.php';
require_once __DIR__ . '/src/Memory/Memory.php';
require_once __DIR__ . '/src/Memory/MemoryToolRegistry.php';

use MCP\Logger\Logger;
use MCP\Memory\Memory;
use MCP\Memory\MemoryToolRegistry;
use MCP\Server\Server;

$server = new Server(logger: new Logger());
$server->registerModel(new Memory(), new MemoryToolRegistry());
$server->listen();
