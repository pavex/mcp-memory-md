<?php

declare(strict_types=1);

namespace MCP\Server;

interface ToolRegistryInterface
{
    /**
     * Returns the list of tool definitions for this model.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;
}
