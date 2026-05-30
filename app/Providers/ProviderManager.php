<?php
declare(strict_types=1);

namespace App\Providers;

use App\Core\Database;

/**
 * ProviderManager - resolves the active data provider chain.
 * Reads provider config from the api_sources table; falls back to MySQL.
 *
 * Active provider: Marketstack (v2). Others (Polygon, Alpha Vantage,
 * Twelve Data, Yahoo Finance, FMP, HKEX) can be added to the factory.
 */
final class ProviderManager
{
    /** @var DataProviderInterface[] */
    private array $chain = [];

    public function __construct()
    {
        // MySQL is always the base provider.
        $this->chain[] = new MysqlProvider();

        // Load enabled external providers from DB config.
        try {
            $db = Database::getInstance();
            $sources = $db->all('SELECT slug, base_url, api_key FROM api_sources WHERE is_enabled = 1 AND status = 1 ORDER BY priority ASC');
            foreach ($sources as $src) {
                $provider = $this->factory($src['slug'], (string) $src['base_url'], (string) $src['api_key']);
                if ($provider && $provider->isEnabled()) {
                    // External providers take precedence over MySQL.
                    array_unshift($this->chain, $provider);
                }
            }
        } catch (\Throwable $e) {
            // Silently fall back to MySQL only.
        }
    }

    private function factory(string $slug, string $baseUrl, string $apiKey): ?DataProviderInterface
    {
        return match ($slug) {
            'marketstack' => new MarketstackProvider($baseUrl ?: 'https://api.marketstack.com/v2', $apiKey),
            // 'polygon', 'alpha-vantage', 'twelve-data', 'fmp', etc. can be added here.
            default       => null,
        };
    }

    public function getQuote(string $symbol): ?array
    {
        foreach ($this->chain as $p) {
            $q = $p->getQuote($symbol);
            if ($q !== null) return $q;
        }
        return null;
    }

    public function getProfile(string $symbol): ?array
    {
        foreach ($this->chain as $p) {
            $r = $p->getProfile($symbol);
            if ($r !== null) return $r;
        }
        return null;
    }

    public function getCandles(string $symbol, string $resolution = 'D', int $limit = 260): array
    {
        foreach ($this->chain as $p) {
            $c = $p->getCandles($symbol, $resolution, $limit);
            if (!empty($c)) return $c;
        }
        return [];
    }
}
