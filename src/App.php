<?php

declare(strict_types=1);

namespace App;

final class App
{
    public static function run(): void
    {
        global $config;

        $request = Request::fromGlobals();

        if ($request->method === 'OPTIONS') {
            Response::noContent();
            return;
        }

        $repository = new ItemRepository($config['data_file']);

        try {
            self::dispatch($request, $repository, $config);
        } catch (\JsonException $exception) {
            Response::json([
                'error' => 'Invalid JSON response',
                'message' => $config['debug'] ? $exception->getMessage() : null,
            ], 500);
        } catch (\Throwable $exception) {
            Response::json([
                'error' => 'Internal server error',
                'message' => $config['debug'] ? $exception->getMessage() : null,
            ], 500);
        }
    }

    private static function dispatch(Request $request, ItemRepository $repository, array $config): void
    {
        if ($request->method === 'GET' && $request->path === '/') {
            Response::json([
                'name' => $config['app_name'],
                'status' => 'ok',
                'docs' => '/docs/',
                'openapi' => '/openapi.json',
                'endpoints' => [
                    'GET /api/health',
                    'GET /api/items',
                    'GET /api/items/{id}',
                    'POST /api/items',
                ],
            ]);
            return;
        }

        if ($request->method === 'GET' && $request->path === '/api/health') {
            Response::json([
                'status' => 'ok',
                'php' => PHP_VERSION,
                'time' => date('c'),
            ]);
            return;
        }

        if ($request->method === 'GET' && $request->path === '/api/items') {
            Response::json([
                'data' => $repository->all(),
            ]);
            return;
        }

        if ($request->method === 'GET' && preg_match('#^/api/items/(\d+)$#', $request->path, $matches) === 1) {
            $item = $repository->find((int) $matches[1]);

            if ($item === null) {
                Response::json(['error' => 'Item not found'], 404);
                return;
            }

            Response::json(['data' => $item]);
            return;
        }

        if ($request->method === 'POST' && $request->path === '/api/items') {
            $title = trim((string) ($request->body['title'] ?? ''));

            if ($title === '') {
                Response::json(['error' => 'Field "title" is required'], 422);
                return;
            }

            $item = $repository->create([
                'title' => $title,
                'description' => (string) ($request->body['description'] ?? ''),
            ]);

            Response::json(['data' => $item], 201);
            return;
        }

        Response::json(['error' => 'Not found'], 404);
    }
}
