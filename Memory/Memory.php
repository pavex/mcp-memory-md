<?php

declare(strict_types=1);

namespace MCP\Memory;

/**
 * Encapsulates all read and write operations on the persistent memory file
 * (memory.md). Keeping this logic in a dedicated class makes it easy to swap
 * the storage backend (e.g. SQLite) without touching the RPC transport layer.
 */
final class Memory
{
    /**
     * Replaces the entire memory file with new Markdown content.
     *
     * @param  string $text  New memory content (must not be empty).
     * @return array{success: bool, message?: string, error?: string}
     */
    public function remember(string $text): array
    {
        if ($text === '') {
            return ['success' => false, 'error' => 'Parameter "text" must not be empty.'];
        }
        file_put_contents(\Config::MEMORY_FILE, trim($text) . PHP_EOL, LOCK_EX);
        return ['success' => true, 'message' => 'Memory overwritten.'];
    }

    /**
     * Appends new Markdown text to the end of the memory file.
     *
     * @param  string $text  Text to append (must not be empty).
     * @return array{success: bool, message?: string, error?: string}
     */
    public function append(string $text): array
    {
        if ($text === '') {
            return ['success' => false, 'error' => 'Parameter "text" must not be empty.'];
        }
        $entry = PHP_EOL . trim($text) . PHP_EOL;
        file_put_contents(\Config::MEMORY_FILE, $entry, FILE_APPEND | LOCK_EX);
        return ['success' => true, 'message' => 'Text appended to memory.'];
    }

    /**
     * Reads and returns the entire content of the memory file.
     *
     * Falls back to default.md on first run, empty string if neither exists.
     *
     * @return array{success: bool, knowledge: string}
     */
    public function knowledge(): array
    {
        if (file_exists(\Config::MEMORY_FILE)) {
            return ['success' => true, 'knowledge' => file_get_contents(\Config::MEMORY_FILE)];
        }
        if (file_exists(\Config::DEFAULT_FILE)) {
            return ['success' => true, 'knowledge' => file_get_contents(\Config::DEFAULT_FILE)];
        }
        return ['success' => true, 'knowledge' => ''];
    }
}
