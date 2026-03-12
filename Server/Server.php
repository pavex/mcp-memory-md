<?php

declare(strict_types=1);

namespace MCP\Server;

use Config;
use MCP\Logger\LoggerInterface;
use MCP\Logger\NullLogger;

/**
 * JSON-RPC 2.0 Server
 *
 * Implements the MCP transport layer: reads newline-delimited JSON-RPC 2.0
 * requests from stdin, dispatches them to registered model handlers, and
 * writes responses to stdout.
 *
 * Models are registered via registerModel() before calling listen().
 * The server has no knowledge of specific models — it only knows how to
 * route tool calls to whichever model registered the matching tool name.
 */
final class Server
{
    /** @var array<string, callable> Map of tool name => handler callable */
    private array $toolHandlers = [];

    /** @var array<int, array<string, mixed>> Aggregated tool definitions from all registries */
    private array $toolDefinitions = [];

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Registers a model and its associated tool registry with the server.
     *
     * @param object                $model    The model instance handling tool calls.
     * @param ToolRegistryInterface $registry Tool definitions exposed by this model.
     */
    public function registerModel(object $model, ToolRegistryInterface $registry): void
    {
        foreach ($registry->all() as $tool) {
            $name = $tool['name'];
            $this->toolDefinitions[] = $tool;
            $this->toolHandlers[$name] = fn(array $params) => $model->{$name}(...array_values($params));
        }
    }

    /**
     * Starts the server's main read loop.
     *
     * Call this after all models have been registered via registerModel().
     */
    public function listen(): void
    {
        while (($line = fgets(STDIN)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $request = json_decode($line, true);
            if ($request === null) {
                $this->sendError(null, -32700, 'JSON parse error.');
                continue;
            }

            $id = $request['id'] ?? null;
            $method = $request['method'] ?? '';
            $params = $request['params'] ?? [];

            $this->dispatch($id, $method, $params);
        }
    }

    // ---------------------------------------------------------------------------
    // Dispatcher
    // ---------------------------------------------------------------------------

    /**
     * @param mixed                $id
     * @param string               $method
     * @param array<string, mixed> $params
     */
    private function dispatch(mixed $id, string $method, array $params): void
    {
        switch ($method) {
            case 'initialize':
                $this->logger->log($method);
                $this->handleInitialize($id, $params);
                break;

            case 'initialized':
                // Notification — no response required per MCP spec.
                break;

            case 'tools/list':
                $this->logger->log($method);
                $this->handleToolsList($id);
                break;

            case 'tools/call':
                $this->handleToolCall($id, $params);
                break;

            case 'ping':
                $this->sendResponse($id, []);
                break;

            default:
                $this->sendError($id, -32601, "Unknown method: {$method}");
        }
    }

    // ---------------------------------------------------------------------------
    // MCP method handlers
    // ---------------------------------------------------------------------------

    /**
     * @param mixed                $id
     * @param array<string, mixed> $params
     */
    private function handleInitialize(mixed $id, array $params): void
    {
        $clientVersion = $params['protocolVersion'] ?? \Config::MCP_PROTOCOL_VERSION;
        $this->sendResponse($id, [
            'protocolVersion' => $clientVersion,
            'capabilities' => ['tools' => new \stdClass()],
            'serverInfo' => [
                'name' => \Config::MCP_SERVER_NAME,
                'version' => \Config::MCP_SERVER_VERSION,
            ],
        ]);
    }

    private function handleToolsList(mixed $id): void
    {
        $this->sendResponse($id, ['tools' => $this->toolDefinitions]);
    }

    /**
     * @param mixed                $id
     * @param array<string, mixed> $params
     */
    private function handleToolCall(mixed $id, array $params): void
    {
        $toolName = $params['name'] ?? '';
        $toolParams = $params['arguments'] ?? [];

        $this->logger->log("tools/call:{$toolName}", $toolParams ?: null);

        if (!isset($this->toolHandlers[$toolName])) {
            $this->sendError($id, -32601, "Unknown tool: {$toolName}");
            return;
        }

        $result = ($this->toolHandlers[$toolName])($toolParams);

        $this->sendResponse($id, [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                ],
            ],
        ]);
    }

    // ---------------------------------------------------------------------------
    // JSON-RPC response helpers
    // ---------------------------------------------------------------------------

    private function sendResponse(mixed $id, mixed $result): void
    {
        echo json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ], JSON_UNESCAPED_UNICODE) . "\n";
    }

    private function sendError(mixed $id, int $code, string $message): void
    {
        if ($id === null) {
            return;
        }
        echo json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => ['code' => $code, 'message' => $message],
        ], JSON_UNESCAPED_UNICODE) . "\n";
    }
}
