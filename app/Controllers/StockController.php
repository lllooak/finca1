<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;
use App\Support\Content;

class StockController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->listView(
            'מניות - מאגר מניות בארה"ב והונג קונג',
            'מאגר מקיף של מניות הנסחרות בבורסות ארה"ב והונג קונג עם נתוני מחיר, שווי שוק, מכפילים, דיבידנד וניתוח פיננסי מעמיק.',
            'WHERE s.status = 1',
            [],
            url('stocks'),
            'כל המניות'
        );
    }

    public function byExchange(array $params): void
    {
        $ex = $this->db->one('SELECT id, name, name_he FROM exchanges WHERE slug = ?', [$params['slug']]);
        if (!$ex) { $this->abort404(); }
        $name = $ex['name_he'] ?: $ex['name'];
        $this->listView("מניות בבורסת {$name}", "כל המניות הנסחרות בבורסת {$name} עם נתונים פיננסיים מלאים.", 'WHERE s.status=1 AND s.exchange_id = ?', [$ex['id']], url('stocks/exchange/' . $params['slug']), "מניות {$name}");
    }

    public function byCountry(array $params): void
    {
        $c = $this->db->one('SELECT id, name, name_he FROM countries WHERE slug = ?', [$params['slug']]);
        if (!$c) { $this->abort404(); }
        $name = $c['name_he'] ?: $c['name'];
        $this->listView("מניות מ{$name}", "מניות חברות מ{$name} עם נתונים פיננסיים מלאים וניתוח.", 'WHERE s.status=1 AND s.country_id = ?', [$c['id']], url('stocks/country/' . $params['slug']), "מניות {$name}");
    }

    private function listView(string $title, string $desc, string $where, array $bind, string $baseUrl, string $crumb): void
    {
        $this->seo->set($title, $desc);
        $this->seo->addBreadcrumb('מניות', SITE_URL . '/stocks');
        if ($crumb !== 'כל המניות') $this->seo->addBreadcrumb($crumb, $baseUrl);

        $sort = request_param('sort', 'market_cap');
        $allowed = ['market_cap', 'day_change_pct', 'volume', 'dividend_yield', 'pe_ratio', 'price'];
        if (!in_array($sort, $allowed, true)) $sort = 'market_cap';
        $dir = request_param('dir', 'desc') === 'asc' ? 'ASC' : 'DESC';

        $page = $this->currentPage();
        $total = $this->db->count("SELECT COUNT(*) FROM stocks s $where", $bind);
        $pg = $this->paginate($total, PER_PAGE, $page);

        $rows = $this->db->all(
            "SELECT s.id, s.ticker, s.company_name, s.company_name_he, s.slug, s.price, s.currency_code,
                    s.day_change_pct, s.market_cap, s.pe_ratio, s.dividend_yield, s.volume
             FROM stocks s $where ORDER BY $sort $dir, s.id ASC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}",
            $bind
        );

        $this->render('stock/index', [
            'title' => $title, 'rows' => $rows, 'pagination' => $pg, 'baseUrl' => $baseUrl, 'sort' => $sort,
        ]);
    }

    public function show(array $params): void
    {
        $ticker = strtoupper($params['ticker']);
        $s = $this->db->one(
            "SELECT s.*, e.name AS exchange_name, e.name_he AS exchange_name_he, e.slug AS exchange_slug,
                    c.name AS country_name, c.name_he AS country_name_he, c.slug AS country_slug,
                    sec.name AS sector_name, sec.name_he AS sector_name_he, sec.slug AS sector_slug,
                    ind.name AS industry_name, ind.name_he AS industry_name_he, ind.slug AS industry_slug
             FROM stocks s
             LEFT JOIN exchanges e ON s.exchange_id = e.id
             LEFT JOIN countries c ON s.country_id = c.id
             LEFT JOIN sectors sec ON s.sector_id = sec.id
             LEFT JOIN industries ind ON s.industry_id = ind.id
             WHERE s.ticker = ? AND s.status = 1",
            [$ticker]
        );
        if (!$s) { $this->abort404(); }

        $name = $s['company_name_he'] ?: $s['company_name'];
        $this->seo->set(
            "{$name} ({$s['ticker']}) מניה - מחיר, נתונים פיננסיים וניתוח",
            "ניתוח מעמיק של מניית {$name} ({$s['ticker']}): מחיר עדכני, שווי שוק, מכפילים, דיבידנד, דוחות כספיים, מתחרים וצפי. " . excerpt($s['description_he'] ?: $s['description'], 120)
        );
        $this->seo->ogType = 'website';
        $this->seo->addBreadcrumb('מניות', SITE_URL . '/stocks');
        if ($s['sector_slug']) $this->seo->addBreadcrumb($s['sector_name_he'] ?: $s['sector_name'], SITE_URL . '/sector/' . $s['sector_slug']);
        $this->seo->addBreadcrumb($s['ticker'], SITE_URL . '/stock/' . $s['ticker']);
        $this->seo->addFinancialProductSchema(['name' => "{$name} ({$s['ticker']})", 'description' => excerpt($s['description_he'] ?: $s['description'], 200), 'category' => 'Stock']);

        $extra = Cache::remember('stock:rel:' . $s['id'], CACHE_TTL_PAGE, function () use ($s) {
            return [
                'financials' => $this->db->all('SELECT * FROM stock_financials WHERE stock_id = ? ORDER BY fiscal_year ASC', [$s['id']]),
                'dividends'  => $this->db->all('SELECT * FROM stock_dividends WHERE stock_id = ? ORDER BY ex_date DESC LIMIT 8', [$s['id']]),
                'prices'     => $this->db->all('SELECT price_date, close FROM stock_prices WHERE stock_id = ? ORDER BY price_date ASC LIMIT 260', [$s['id']]),
                'competitors'=> $this->db->all(
                    'SELECT ticker, company_name, company_name_he, slug, price, currency_code, day_change_pct, market_cap, pe_ratio, dividend_yield, volume
                     FROM stocks WHERE sector_id <=> ? AND id != ? AND status=1 ORDER BY ABS(COALESCE(market_cap,0) - ?) ASC LIMIT 8',
                    [$s['sector_id'], $s['id'], $s['market_cap'] ?? 0]
                ),
                'themes'     => $this->db->all('SELECT t.name, t.name_he, t.slug FROM themes t JOIN stock_themes st ON t.id=st.theme_id WHERE st.stock_id = ?', [$s['id']]),
                'etfs'       => $this->db->all('SELECT DISTINCT e.ticker, e.name, e.name_he, e.slug, h.weight FROM etf_holdings h JOIN etfs e ON h.etf_id=e.id WHERE h.stock_id = ? ORDER BY h.weight DESC LIMIT 8', [$s['id']]),
            ];
        });

        $faqs = $this->faqs('stock', (int) $s['id']);
        if (empty($faqs)) {
            $faqs = Content::stockFaqs($s);
        }
        $this->seo->addFaqSchema($faqs);

        $this->render('stock/show', array_merge(['s' => $s, 'faqs' => $faqs], $extra));
    }

    public function chart(array $params): void
    {
        $ticker = strtoupper($params['ticker']);
        $id = $this->db->value('SELECT id FROM stocks WHERE ticker = ? AND status=1', [$ticker]);
        if (!$id) { $this->json(['labels' => [], 'data' => []]); }
        $rows = $this->db->all('SELECT price_date, close FROM stock_prices WHERE stock_id = ? ORDER BY price_date ASC LIMIT 260', [$id]);
        $this->json([
            'labels' => array_map(fn($r) => $r['price_date'], $rows),
            'data'   => array_map(fn($r) => (float) $r['close'], $rows),
        ]);
    }
}
