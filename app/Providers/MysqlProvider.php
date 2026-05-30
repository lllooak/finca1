<?php
declare(strict_types=1);

namespace App\Providers;

use App\Core\Database;

/**
 * MysqlProvider - default provider serving data from the local MySQL database.
 * This is the active provider; all pages render from this source.
 */
final class MysqlProvider implements DataProviderInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function name(): string
    {
        return 'mysql';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function getQuote(string $symbol): ?array
    {
        $row = $this->db->one(
            'SELECT ticker AS symbol, price, day_change AS change, day_change_pct AS change_pct, day_high AS high, day_low AS low, volume, prev_close, currency_code FROM stocks WHERE ticker = ? AND status=1',
            [strtoupper($symbol)]
        );
        return $row ?: null;
    }

    public function getProfile(string $symbol): ?array
    {
        return $this->db->one(
            'SELECT ticker AS symbol, company_name AS name, company_name_he AS name_he, market_cap, sector_id, industry_id, country_id, website, employees, description, description_he FROM stocks WHERE ticker = ? AND status=1',
            [strtoupper($symbol)]
        ) ?: null;
    }

    public function getCandles(string $symbol, string $resolution = 'D', int $limit = 260): array
    {
        $id = $this->db->value('SELECT id FROM stocks WHERE ticker = ? AND status=1', [strtoupper($symbol)]);
        if (!$id) {
            return [];
        }
        return $this->db->all(
            'SELECT price_date AS t, open AS o, high AS h, low AS l, close AS c, volume AS v FROM stock_prices WHERE stock_id = ? ORDER BY price_date ASC LIMIT ?',
            [$id, $limit]
        );
    }
}
