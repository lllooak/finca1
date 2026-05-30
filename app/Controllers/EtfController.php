<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;
use App\Support\Content;

class EtfController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('קרנות סל (ETF) - מאגר ETF מקיף', 'מאגר קרנות סל (ETF) הנסחרות בבורסות ארה"ב: נכסים מנוהלים, דמי ניהול, מדד ייחוס, החזקות ותשואות.');
        $this->seo->addBreadcrumb('קרנות סל', SITE_URL . '/etfs');

        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM etfs WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all(
            'SELECT ticker, name, name_he, slug, issuer, price, currency_code, day_change_pct, aum, expense_ratio, dividend_yield, asset_class
             FROM etfs WHERE status=1 ORDER BY aum DESC, id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']
        );
        $this->render('etf/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('etfs')]);
    }

    public function show(array $params): void
    {
        $ticker = strtoupper($params['ticker']);
        $e = $this->db->one(
            'SELECT e.*, ex.name AS exchange_name, ex.name_he AS exchange_name_he
             FROM etfs e LEFT JOIN exchanges ex ON e.exchange_id=ex.id WHERE e.ticker = ? AND e.status=1',
            [$ticker]
        );
        if (!$e) { $this->abort404(); }

        $name = $e['name_he'] ?: $e['name'];
        $this->seo->set("{$name} ({$e['ticker']}) ETF - החזקות, דמי ניהול וביצועים", "ניתוח קרן הסל {$name} ({$e['ticker']}): נכסים מנוהלים, דמי ניהול, החזקות עיקריות, פיזור סקטוריאלי וגיאוגרפי, תשואות וסיכון.");
        $this->seo->addBreadcrumb('קרנות סל', SITE_URL . '/etfs');
        $this->seo->addBreadcrumb($e['ticker'], SITE_URL . '/etf/' . $e['ticker']);
        $this->seo->addFinancialProductSchema(['name' => "{$name} ({$e['ticker']})", 'description' => excerpt($e['description_he'] ?: $e['description'], 200), 'category' => 'ETF']);

        $rel = Cache::remember('etf:rel:' . $e['id'], CACHE_TTL_PAGE, function () use ($e) {
            return [
                'holdings' => $this->db->all('SELECT holding_name, holding_ticker, weight, sector, country, stock_id FROM etf_holdings WHERE etf_id = ? ORDER BY weight DESC LIMIT 25', [$e['id']]),
                'similar'  => $this->db->all('SELECT ticker, name, name_he, slug, aum, expense_ratio, day_change_pct, currency_code FROM etfs WHERE id != ? AND (asset_class <=> ? OR category <=> ?) AND status=1 ORDER BY aum DESC LIMIT 8', [$e['id'], $e['asset_class'], $e['category']]),
            ];
        });

        // sector allocation aggregated from holdings
        $alloc = [];
        foreach ($rel['holdings'] as $h) {
            $sec = $h['sector'] ?: 'אחר';
            $alloc[$sec] = ($alloc[$sec] ?? 0) + (float) $h['weight'];
        }
        arsort($alloc);
        $alloc = array_slice($alloc, 0, 8, true);

        $faqs = $this->faqs('etf', (int) $e['id']);
        if (empty($faqs)) $faqs = Content::etfFaqs($e);
        $this->seo->addFaqSchema($faqs);

        $this->render('etf/show', array_merge(['e' => $e, 'faqs' => $faqs, 'alloc' => $alloc], $rel));
    }
}
