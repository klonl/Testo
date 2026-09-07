<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class JsonStorage
{
    protected string $disk = 'local';
    protected string $path;

    public function __construct(string $filename)
    {
        $this->path = $filename;
    }

    /**
     * Читает данные из JSON-файла с блокировкой.
     */
    public function read(): array
    {
        $fullPath = storage_path('app/' . $this->path);
        if (!file_exists($fullPath)) {
            return [];
        }
        $fp = fopen($fullPath, 'r');
        if (!flock($fp, LOCK_SH)) {
            fclose($fp);
            throw new RuntimeException('Не удалось получить блокировку для чтения');
        }
        $content = file_get_contents($fullPath);
        flock($fp, LOCK_UN);
        fclose($fp);
        return json_decode($content, true) ?? [];
    }

    /**
     * Записывает данные в JSON-файл с эксклюзивной блокировкой.
     */
    public function write(array $data): void
    {
        $fullPath = storage_path('app/' . $this->path);
        $fp = fopen($fullPath, 'c+');
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            throw new RuntimeException('Не удалось получить блокировку для записи');
        }
        ftruncate($fp, 0);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * Атомарное обновление: чтение, модификация, запись.
     */
    public function update(callable $callback): array
    {
        $this->write($callback($this->read()));
        return $this->read();
    }
}
