<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;

class MarketController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('סקירת שוק - מדדים, מובילים ומגזרים', 'סקירת שוק כוללת: מדדים מובילים, מניות עולות ויורדות, הנסחרות ביותר וביצועי סקטורים בשוק האמריקאי ובהונג קונג.');
        $this->seo->addBreadcrumb('סקירת שוק', SITE_URL . '/markets');

        $data = Cache::remember('markets:v1', CACHE_TTL_LIST, function () {
            $base = "SELECT ticker, company_name, company_name_he, slug, price, currency_code, day_change_pct, market_cap, pe_ratio, volume FROM stocks WHERE status=1";
            return [
                'indices' => $this->db->all('SELECT name, name_he, slug, symbol, value, day_change_pct, ytd_return FROM indices WHERE status=1 ORDER BY is_featured DESC, id ASC LIMIT 12'),
                'gainers' => $this->db->all("$base AND day_change_pct IS NOT NULL ORDER BY day_change_pct DESC LIMIT 15"),
                'losers'  => $this->db->all("$base AND day_change_pct IS NOT NULL ORDER BY day_change_pct ASC LIMIT 15"),
                'active'  => $this->db->all("$base AND volume IS NOT NULL ORDER BY volume DESC LIMIT 15"),
                'sectors' => $this->db->all('SELECT name, name_he, slug, icon FROM sectors WHERE status=1 ORDER BY id ASC LIMIT 12'),
            ];
        });

        $this->render('market/index', $data);
    }

    public function movers(array $params = []): void
    {
        $ticker = Cache::remember('ticker:tape', 120, function () {
            $rows = $this->db->all("SELECT symbol, name, value AS price, day_change_pct FROM indices WHERE status=1 ORDER BY is_featured DESC LIMIT 6");
            $stocks = $this->db->all("SELECT ticker AS symbol, price, day_change_pct FROM stocks WHERE status=1 AND market_cap IS NOT NULL ORDER BY market_cap DESC LIMIT 12");
            $out = [];
            foreach ($rows as $r) {
                $out[] = ['symbol' => $r['symbol'] ?: $r['name'], 'price' => number_format((float)$r['price'], 2), 'change' => (float)($r['day_change_pct'] ?? 0)];
            }
            foreach ($stocks as $s) {
                $out[] = ['symbol' => $s['symbol'], 'price' => number_format((float)$s['price'], 2), 'change' => (float)($s['day_change_pct'] ?? 0)];
            }
            return $out;
        });
        $this->json(['ticker' => $ticker]);
    }
}
