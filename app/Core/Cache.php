<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cache - Simple, fast file-based cache.
 * Designed for high-traffic content pages (100k+ pages).
 */
final class Cache
{
    private static string $dir = CACHE_PATH;

    private static function path(string $key): string
    {
        $hash = hash('xxh3', $key) ?: md5($key);
        // shard into subdirectories to avoid huge directories
        $shard = substr($hash, 0, 2);
        $dir = self::$dir . '/' . $shard;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir . '/' . $hash . '.cache';
    }

    public static function get(string $key)
    {
        if (!CACHE_ENABLED) {
            return null;
        }
        $file = self::path($key);
        if (!is_file($file)) {
            return null;
        }
        $raw = @file_get_contents($file);
        if ($raw === false) {
            return null;
        }
        $data = @unserialize($raw);
        if (!is_array($data) || !isset($data['expires'], $data['value'])) {
            return null;
        }
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            @unlink($file);
            return null;
        }
        return $data['value'];
    }

    public static function set(string $key, $value, int $ttl = CACHE_TTL): bool
    {
        if (!CACHE_ENABLED) {
            return false;
        }
        $payload = serialize([
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'value'   => $value,
        ]);
        return @file_put_contents(self::path($key), $payload, LOCK_EX) !== false;
    }

    /**
     * Remember pattern - fetch from cache or compute and store.
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function forget(string $key): void
    {
        $file = self::path($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public static function flush(): int
    {
        $count = 0;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::$dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                @unlink($file->getPathname());
                $count++;
            }
        }
        return $count;
    }
}
