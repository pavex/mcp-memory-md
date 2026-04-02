<?php

declare(strict_types=1);

namespace MCP\Memory;

/**
 * Encapsulates all read and write operations on the persistent memory file.
 * Keeping this logic in a dedicated class makes it easy to swap the storage
 * backend (e.g. SQLite) without touching the RPC transport layer.
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
        if ($error = $this->directoryError()) {
            return ['success' => false, 'error' => $error];
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
        if ($error = $this->directoryError()) {
            return ['success' => false, 'error' => $error];
        }
        $entry = PHP_EOL . trim($text) . PHP_EOL;
        file_put_contents(\Config::MEMORY_FILE, $entry, FILE_APPEND | LOCK_EX);
        return ['success' => true, 'message' => 'Text appended to memory.'];
    }

    /**
     * Reads and returns the entire content of the memory file.
     *
     * In prefix mode falls back to <prefix>-default.md on first run.
     * Returns an empty string if neither file exists.
     *
     * @return array{success: bool, knowledge: string}
     */
    public function knowledge(): array
    {
        if (file_exists(\Config::MEMORY_FILE)) {
            return ['success' => true, 'knowledge' => file_get_contents(\Config::MEMORY_FILE)];
        }
        if (\Config::DEFAULT_FILE !== '' && file_exists(\Config::DEFAULT_FILE)) {
            return ['success' => true, 'knowledge' => file_get_contents(\Config::DEFAULT_FILE)];
        }
        return ['success' => true, 'knowledge' => ''];
    }

    /**
     * Returns an error message if the target directory does not exist or is not writable,
     * null otherwise. The directory must be created and configured by the user beforehand.
     */
    private function directoryError(): ?string
    {
        $dir = dirname(\Config::MEMORY_FILE);
        if (!is_dir($dir)) {
            return "Directory does not exist: {$dir}";
        }
        if (!is_writable($dir)) {
            return "Directory is not writable: {$dir}";
        }
        return null;
    }
}
