<?php
declare(strict_types=1);

namespace App\Controllers;

class CurrencyController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('מטבעות - שערי חליפין', 'מאגר מטבעות וזוגות מטבע: שערי חליפין, שינויים ונתונים על שוק המט"ח.');
        $this->seo->addBreadcrumb('מטבעות', SITE_URL . '/currencies');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM currencies WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all('SELECT name, name_he, slug, code, pair, symbol, rate, day_change_pct FROM currencies WHERE status=1 ORDER BY id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('currency/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('currencies')]);
    }

    public function show(array $params): void
    {
        $c = $this->db->one('SELECT * FROM currencies WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$c) { $this->abort404(); }
        $name = $c['name_he'] ?: $c['name'];
        $this->seo->set("{$name} - שער חליפין ונתונים", "שער {$name} ({$c['pair']}), שינוי יומי ונתונים על המטבע.");
        $this->seo->addBreadcrumb('מטבעות', SITE_URL . '/currencies');
        $this->seo->addBreadcrumb($name, SITE_URL . '/currency/' . $c['slug']);
        $faqs = $this->faqs('currency', (int) $c['id']);
        $this->seo->addFaqSchema($faqs);
        $similar = $this->db->all('SELECT name, name_he, slug, pair, rate, day_change_pct FROM currencies WHERE id != ? AND status=1 LIMIT 8', [$c['id']]);
        $this->render('currency/show', ['c' => $c, 'faqs' => $faqs, 'similar' => $similar]);
    }
}
