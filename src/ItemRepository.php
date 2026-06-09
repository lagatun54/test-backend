<?php

declare(strict_types=1);

namespace App;

final class ItemRepository
{
    public function __construct(
        private readonly string $dataFile,
    ) {
    }

    /** @return list<array{id: int, title: string, description: string}> */
    public function all(): array
    {
        return $this->read();
    }

    public function find(int $id): ?array
    {
        foreach ($this->read() as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /** @param array{title: string, description?: string} $data */
    public function create(array $data): array
    {
        $items = $this->read();
        $nextId = 1;

        foreach ($items as $item) {
            $nextId = max($nextId, $item['id'] + 1);
        }

        $item = [
            'id' => $nextId,
            'title' => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
        ];

        $items[] = $item;
        $this->write($items);

        return $item;
    }

    /** @return list<array{id: int, title: string, description: string}> */
    private function read(): array
    {
        if (!is_file($this->dataFile)) {
            return [];
        }

        $json = file_get_contents($this->dataFile);
        $data = json_decode($json ?: '[]', true);

        return is_array($data) ? $data : [];
    }

    /** @param list<array{id: int, title: string, description: string}> $items */
    private function write(array $items): void
    {
        $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        file_put_contents($this->dataFile, $json . PHP_EOL, LOCK_EX);
    }
}
