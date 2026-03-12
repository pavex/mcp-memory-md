<?php

declare(strict_types=1);

namespace MCP\Memory;

use MCP\Server\ToolRegistryInterface;

/**
 * Describes the MCP tools exposed by the Memory model:
 * remember, append, and knowledge.
 * Each entry follows the MCP inputSchema (JSON Schema) convention.
 */
final class MemoryToolRegistry implements ToolRegistryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            [
                'name' => 'remember',
                'description' =>
                    'Replaces the entire content of long-term memory (memory.md) with new text. '
                    . 'Use this to reorganise or rewrite all stored knowledge at once. '
                    . 'Destructive — all previous content is permanently lost. '
                    . 'Prefer "append" when you only want to add new information.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'text' => [
                            'type' => 'string',
                            'description' => 'New full content of memory (replaces everything currently stored).',
                        ],
                    ],
                    'required' => ['text'],
                ],
            ],
            [
                'name' => 'append',
                'description' =>
                    'Appends new text to the end of long-term memory (memory.md). '
                    . 'Use this to add new facts or notes without losing existing knowledge. '
                    . 'Safe — existing content is never modified or removed.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'text' => [
                            'type' => 'string',
                            'description' => 'Text to append at the end of memory.',
                        ],
                    ],
                    'required' => ['text'],
                ],
            ],
            [
                'name' => 'knowledge',
                'description' =>
                    'Reads and returns the entire content of long-term memory (memory.md). '
                    . 'Call this at the beginning of every conversation to restore context from previous sessions. '
                    . 'Read-only — no side effects.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                    'required' => [],
                ],
            ],
        ];
    }
}
