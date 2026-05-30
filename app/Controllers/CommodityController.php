<?php
declare(strict_types=1);

namespace App\Controllers;

class CommodityController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('סחורות - נפט, זהב, כסף ועוד', 'מאגר סחורות: אנרגיה, מתכות יקרות, מתכות תעשייתיות וחקלאות. מחירים ונתונים.');
        $this->seo->addBreadcrumb('סחורות', SITE_URL . '/commodities');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM commodities WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all('SELECT name, name_he, slug, symbol, category, unit, price, currency_code, day_change_pct, ytd_return FROM commodities WHERE status=1 ORDER BY id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('commodity/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('commodities')]);
    }

    public function show(array $params): void
    {
        $c = $this->db->one('SELECT * FROM commodities WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$c) { $this->abort404(); }
        $name = $c['name_he'] ?: $c['name'];
        $this->seo->set("{$name} - מחיר סחורה ונתונים", "מחיר {$name}, שינוי יומי, תשואה ונתונים על מסחר בסחורה.");
        $this->seo->addBreadcrumb('סחורות', SITE_URL . '/commodities');
        $this->seo->addBreadcrumb($name, SITE_URL . '/commodity/' . $c['slug']);
        $faqs = $this->faqs('commodity', (int) $c['id']);
        $this->seo->addFaqSchema($faqs);
        $similar = $this->db->all('SELECT name, name_he, slug, price, currency_code, day_change_pct FROM commodities WHERE id != ? AND category <=> ? AND status=1 LIMIT 8', [$c['id'], $c['category']]);
        $this->render('commodity/show', ['c' => $c, 'faqs' => $faqs, 'similar' => $similar]);
    }
}
