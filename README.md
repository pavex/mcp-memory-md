# 🧠 Persistent Memory for Claude via MCP

> **PHP MCP server that gives Claude long-term memory stored in a local Markdown file**

`mcp-memory-md` is a lightweight [Model Context Protocol (MCP)](https://modelcontextprotocol.io) server written in pure PHP. It enables Claude to remember information across conversations by reading and writing a local `memory.md` file — no database, no cloud, no dependencies.

Built as a clean, readable demonstration of how MCP works: JSON-RPC 2.0 transport, tool definitions, and persistent storage — all in a handful of PHP classes.

**Keywords:** MCP server, Claude memory, persistent memory, Claude Desktop, PHP MCP, long-term memory, Model Context Protocol, AI memory, claude-desktop, memory plugin

---

## ✨ Features

- 📝 Persistent memory stored in a plain Markdown file
- 🔧 Zero dependencies — pure PHP 8.1+, no Composer required
- 🚀 Three focused tools: `knowledge`, `remember`, `append`
- 📋 Customisable first-run template via `default.md`
- 👁️ Real-time memory watcher (`watch.php`) for live monitoring
- 🗂️ Multiple isolated memory files via optional prefix argument
- 🔌 Compatible with Claude Desktop and any MCP-capable client

---

## 📁 Project Structure

```
mcp-memory-md/
├── Logger/
│   ├── LoggerInterface.php    # Logger contract
│   ├── Logger.php             # File logger with automatic rotation
│   └── NullLogger.php         # No-op logger (default)
├── Memory/
│   ├── Memory.php             # Memory model — remember, append, knowledge
│   └── MemoryToolRegistry.php # Tool definitions for the Memory model
├── Server/
│   ├── ToolRegistryInterface.php # Tool registry contract
│   └── Server.php             # JSON-RPC 2.0 transport and dispatcher
├── .storage/
│   ├── default.md             # Default instructions loaded on first run
│   ├── memory.md              # Persistent memory file (auto-generated)
│   └── memory.log             # Tool call log (auto-generated)
├── Config.php                 # Class constants — paths, limits, protocol version
├── mcp.php                    # Entry point — registers models and starts the server
└── watch.php                  # CLI watcher — displays memory.md on every change
```

### Available Tools

| Tool | Description |
|---|---|
| `knowledge` | Reads the entire content of `memory.md` — call at the start of each conversation |
| `remember` | Replaces the entire content of `memory.md` with new text — use to reorganise or rewrite all stored knowledge |
| `append` | Adds text to the end of `memory.md` without losing existing content |

---

## ⚙️ Requirements

- **PHP 8.1+** (CLI)
- **OS:** Linux / macOS / Windows (WSL recommended)
- **Claude Desktop** or any other MCP-compatible client

Verify your PHP version:
```bash
php --version
```

---

## 🚀 Installation

```bash
# Clone the repository
git clone https://github.com/pavex/mcp-memory-md.git
cd mcp-memory-md

# Make the entry point executable
chmod +x mcp.php
```

No dependencies, no Composer — pure PHP only.

---

## 🔧 Configuration

All paths and limits are defined as class constants in `Config.php`:

```php
Config::MEMORY_FILE       // main memory file  (.storage/memory.md)
Config::DEFAULT_FILE      // default template  (.storage/default.md)
Config::LOG_FILE          // tool call log     (.storage/memory.log)
Config::LOG_MAX_BYTES     // rotate log after 32 KB
Config::WATCH_INTERVAL_US // watcher poll interval (500 ms)
```

---

## 📋 First Run — default.md

On first use, `memory.md` does not exist yet. When `knowledge` is called, the server
automatically loads `.storage/default.md` instead — a template with base instructions that guides
Claude on how to structure and maintain memory.

Once Claude writes anything via `append` or `remember`, `memory.md` is created and
`default.md` is no longer used. You can freely edit `default.md` to customise the
initial instructions for your own setup.

---

## 🔌 Connecting to Claude Desktop

Open the Claude Desktop configuration file:

- **macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Windows:** `%APPDATA%\Claude\claude_desktop_config.json`
- **Linux:** `~/.config/Claude/claude_desktop_config.json`

Add the server under `mcpServers`:

```json
{
  "mcpServers": {
    "memory-md": {
      "command": "php",
      "args": ["/absolute/path/to/mcp-memory-md/mcp.php"]
    }
  }
}
```

> ⚠️ Use an absolute path. Relative paths will not work.

After saving, **restart Claude Desktop**. The tools icon (🔨) will appear in the interface — click it to confirm the server is running and tools are available.

---

## 🗂️ Multiple Memory Files (prefix argument)

By default the server stores memory in `.storage/memory.md`. You can pass an optional
prefix as the first argument to `mcp.php` to use a different file — allowing you to run
multiple isolated server instances from the same installation.

**Allowed characters:** letters, digits, hyphen, underscore (`a-z A-Z 0-9 - _`).
Invalid or missing prefix falls back to `memory`.

### File mapping

| Argument | Memory file | Log file |
|---|---|---|
| *(none)* | `.storage/memory.md` | `.storage/memory.log` |
| `longterm` | `.storage/longterm.md` | `.storage/longterm.log` |
| `shared` | `.storage/shared.md` | `.storage/shared.log` |

### Claude Desktop — two independent servers

```json
{
  "mcpServers": {
    "memory-md": {
      "command": "php",
      "args": ["/absolute/path/to/mcp-memory-md/mcp.php"]
    },
    "memory-longterm": {
      "command": "php",
      "args": ["/absolute/path/to/mcp-memory-md/mcp.php", "longterm"]
    }
  }
}
```

Each server exposes the same three tools (`knowledge`, `remember`, `append`) but reads
and writes its own isolated file. Claude Desktop will list both under the tools icon.

---

## 💬 Usage

### Basic Instructions for Claude

Add the following to your system prompt or first message:

```
Always call the `knowledge` tool at the start of each conversation to load your memory.
Save important information using `append` or `remember`.
```

### Example Conversation

**User:** Remember that I prefer responses in English and a concise style.

**Claude** *(calls `append`)*:
```markdown
## User Preferences
- Language: English
- Style: concise, no unnecessary filler
```

**Claude:** Got it. ✓

---

## 👁️ Watching Memory in Real Time

`watch.php` is a CLI utility that monitors `memory.md` and reprints its content
to the terminal whenever the file changes. Useful for seeing exactly what Claude
is writing during an active session.

**Start the watcher:**
```bash
php watch.php
```

The terminal clears and displays the current content of `memory.md` on every change.
Press `Ctrl+C` to stop.

**Typical workflow:**
1. Open two terminal windows side by side.
2. Run `php watch.php` in one window.
3. Start a conversation with Claude in the other.
4. Watch memory update in real time as Claude calls `append` or `remember`.

> 💡 The watcher polls for file changes every 500 ms (configurable via `Config::WATCH_INTERVAL_US`).

---

## 📋 Example memory.md

```markdown
# Memory — Claude

## Identity
- Name: Pavel
- Language: Czech
- Style: concise, no unnecessary filler

## Projects
- mcp-memory-md: MCP server for persistent memory
- pavex-vcl: TypeScript component framework

## TODO
- Fix removeChild() in Container.ts
```

---

## 📜 License

**MIT License** — free to use for personal and educational purposes.

```
MIT License

Copyright (c) 2026 pavex@ines.cz

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

*Minimal, readable, zero-dependency MCP memory server for Claude — built in pure PHP.*
