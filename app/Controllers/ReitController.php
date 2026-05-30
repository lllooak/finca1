<?php
declare(strict_types=1);

namespace App\Controllers;

class ReitController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('REIT - קרנות נדל"ן מניב', 'מאגר קרנות REIT: נדל"ן מסחרי, מגורים, תעשייה, מרכזי נתונים ועוד. תשואת דיבידנד, FFO ושיעורי תפוסה.');
        $this->seo->addBreadcrumb('REIT', SITE_URL . '/reits');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM reits WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all('SELECT ticker, name, name_he, slug, property_type, price, currency_code, day_change_pct, market_cap, dividend_yield, occupancy_rate FROM reits WHERE status=1 ORDER BY market_cap DESC, id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('reit/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('reits')]);
    }

    public function show(array $params): void
    {
        $ticker = strtoupper($params['ticker']);
        $r = $this->db->one('SELECT r.*, e.name AS exchange_name, e.name_he AS exchange_name_he FROM reits r LEFT JOIN exchanges e ON r.exchange_id=e.id WHERE r.ticker = ? AND r.status=1', [$ticker]);
        if (!$r) { $this->abort404(); }
        $name = $r['name_he'] ?: $r['name'];
        $this->seo->set("{$name} ({$r['ticker']}) REIT - דיבידנד, FFO וניתוח", "ניתוח קרן הריט {$name} ({$r['ticker']}): תשואת דיבידנד, FFO, שיעור תפוסה, סוג נכסים וביצועים.");
        $this->seo->addBreadcrumb('REIT', SITE_URL . '/reits');
        $this->seo->addBreadcrumb($r['ticker'], SITE_URL . '/reit/' . $r['ticker']);
        $this->seo->addFinancialProductSchema(['name' => "{$name} ({$r['ticker']})", 'category' => 'REIT']);
        $faqs = $this->faqs('reit', (int) $r['id']);
        if (empty($faqs)) {
            $faqs = [
                ['question' => "מהי תשואת הדיבידנד של {$name}?", 'answer' => "תשואת הדיבידנד עומדת על " . num($r['dividend_yield']) . "%."],
                ['question' => "באיזה סוג נדל\"ן מתמקדת {$name}?", 'answer' => "הקרן מתמקדת בנדל\"ן מסוג {$r['property_type']}."],
            ];
        }
        $this->seo->addFaqSchema($faqs);
        $similar = $this->db->all('SELECT ticker, name, name_he, slug, price, currency_code, day_change_pct, dividend_yield FROM reits WHERE id != ? AND property_type <=> ? AND status=1 ORDER BY market_cap DESC LIMIT 8', [$r['id'], $r['property_type']]);
        $this->render('reit/show', ['r' => $r, 'faqs' => $faqs, 'similar' => $similar]);
    }
}
