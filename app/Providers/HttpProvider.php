<?php
declare(strict_types=1);

namespace App\Providers;

/**
 * HttpProvider - abstract base for external REST data providers
 * (Marketstack, Polygon, Alpha Vantage, Twelve Data, FMP,
 * IEX Cloud, Yahoo Finance, HKEX, etc.).
 *
 * Concrete providers extend this class and implement endpoint mapping.
 * Currently disabled by default; enable via api_sources table + config.
 */
abstract class HttpProvider implements DataProviderInterface
{
    protected string $baseUrl = '';
    protected string $apiKey = '';
    protected int $timeout = 8;

    public function __construct(string $baseUrl = '', string $apiKey = '')
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    public function isEnabled(): bool
    {
        return $this->apiKey !== '' && $this->baseUrl !== '';
    }

    /** Perform a GET request and decode JSON. */
    protected function get(string $path, array $query = []): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        if ($query) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $ctx = stream_context_create(['http' => [
            'method'  => 'GET',
            'timeout' => $this->timeout,
            'header'  => "Accept: application/json\r\n",
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            logger("HTTP provider request failed: {$url}", 'WARN');
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}
