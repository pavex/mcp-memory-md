#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/Config.php';

/**
 * Memory MD Server — Memory Watcher
 *
 * CLI utility that watches memory.md and reprints its content to the terminal
 * whenever the file changes. Useful for monitoring what Claude writes during
 * an active session.
 *
 * Usage: php watch.php
 * Exit:  Ctrl+C
 */
final class Watcher
{
    private ?int $lastMtime = null;

    public function __construct(private readonly string $file) {}

    /**
     * Starts the watch loop. Runs until the process is terminated (Ctrl+C).
     */
    public function run(): void
    {
        echo "Watching: {$this->file}\n";
        sleep(1);
        while (true) {
            clearstatcache(true, $this->file);
            $mtime = file_exists($this->file) ? filemtime($this->file) : null;
            if ($mtime !== $this->lastMtime) {
                $this->lastMtime = $mtime;
                $this->display();
            }
            usleep(Config::WATCH_INTERVAL_US);
        }
    }

    /**
     * Clears the terminal and prints the current content of the watched file.
     */
    private function display(): void
    {
        echo "\033[2J\033[H";
        echo "\033[1;36m=== " . basename($this->file) . " ===\033[0m\n";
        echo "\033[0;33m" . date('H:i:s') . "\033[0m\n\n";
        if (!file_exists($this->file)) {
            echo "\033[1;31mFile not found: {$this->file}\033[0m\n";
            return;
        }
        echo file_get_contents($this->file);
        echo "\n\n\033[0;90m[Waiting... Ctrl+C to terminate.]\033[0m\n";
    }
}

(new Watcher(Config::MEMORY_FILE))->run();
