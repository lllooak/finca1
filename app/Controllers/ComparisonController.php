<?php
declare(strict_types=1);

namespace App\Controllers;

class ComparisonController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('השוואות - השוואת מניות, ETF ומדדים', 'מאגר השוואות פיננסיות: השוואת מניות, קרנות סל, מדדים וקריפטו זו מול זו לפי תמחור, צמיחה, רווחיות וביצועים.');
        $this->seo->addBreadcrumb('השוואות', SITE_URL . '/compare');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM comparisons WHERE status=1');
        $pg = $this->paginate($total, 24, $page);
        $rows = $this->db->all('SELECT title, slug, summary, asset_type FROM comparisons WHERE status=1 ORDER BY is_featured DESC, id DESC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('comparison/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('compare')]);
    }

    public function show(array $params): void
    {
        $c = $this->db->one('SELECT * FROM comparisons WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$c) { $this->abort404(); }
        $this->db->run('UPDATE comparisons SET views = views + 1 WHERE id = ?', [$c['id']]);
        $this->seo->set($c['meta_title'] ?: $c['title'], $c['meta_desc'] ?: excerpt($c['summary'], 200));
        $this->seo->addBreadcrumb('השוואות', SITE_URL . '/compare');
        $this->seo->addBreadcrumb($c['title'], SITE_URL . '/compare/' . $c['slug']);
        $faqs = $this->faqs('comparison', (int) $c['id']);
        $this->seo->addFaqSchema($faqs);

        // Load entities if they are stocks
        $a = $b = null;
        if ($c['entity_a_type'] === 'stock' && $c['entity_a_id']) $a = $this->loadStock((int) $c['entity_a_id']);
        if ($c['entity_b_type'] === 'stock' && $c['entity_b_id']) $b = $this->loadStock((int) $c['entity_b_id']);

        $this->render('comparison/show', ['c' => $c, 'a' => $a, 'b' => $b, 'faqs' => $faqs]);
    }

    /** Dynamic on-the-fly comparison of two stock tickers: /vs/AAPL/MSFT */
    public function dynamic(array $params): void
    {
        $a = $this->loadStockByTicker(strtoupper($params['a']));
        $b = $this->loadStockByTicker(strtoupper($params['b']));
        if (!$a || !$b) { $this->abort404(); }

        $na = $a['company_name_he'] ?: $a['company_name'];
        $nb = $b['company_name_he'] ?: $b['company_name'];
        $title = "{$a['ticker']} מול {$b['ticker']} - השוואת מניות ({$na} vs {$nb})";
        $this->seo->set($title, "השוואה מקיפה בין מניית {$na} ({$a['ticker']}) למניית {$nb} ({$b['ticker']}): תמחור, צמיחה, רווחיות, דיבידנד וביצועים. מי עדיפה להשקעה?");
        $this->seo->addBreadcrumb('השוואות', SITE_URL . '/compare');
        $this->seo->addBreadcrumb("{$a['ticker']} vs {$b['ticker']}", SITE_URL . '/vs/' . $a['ticker'] . '/' . $b['ticker']);

        $faqs = [
            ['question' => "מי גדולה יותר, {$na} או {$nb}?", 'answer' => (($a['market_cap'] ?? 0) >= ($b['market_cap'] ?? 0) ? $na : $nb) . " גדולה יותר מבחינת שווי שוק."],
            ['question' => "איזו מניה זולה יותר לפי מכפיל רווח?", 'answer' => "מכפיל הרווח של {$a['ticker']} הוא " . num($a['pe_ratio']) . " ושל {$b['ticker']} הוא " . num($b['pe_ratio']) . "."],
        ];
        $this->seo->addFaqSchema($faqs);

        $c = [
            'title' => $title,
            'summary' => "השוואה דינמית בין {$na} ל{$nb}.",
            'content' => '', 'pros_a' => '', 'cons_a' => '', 'pros_b' => '', 'cons_b' => '', 'verdict' => '',
            'entity_a_label' => "{$na} ({$a['ticker']})",
            'entity_b_label' => "{$nb} ({$b['ticker']})",
        ];
        $this->render('comparison/show', ['c' => $c, 'a' => $a, 'b' => $b, 'faqs' => $faqs, 'dynamic' => true]);
    }

    private function loadStock(int $id): ?array
    {
        return $this->db->one('SELECT * FROM stocks WHERE id = ? AND status=1', [$id]);
    }

    private function loadStockByTicker(string $ticker): ?array
    {
        return $this->db->one('SELECT * FROM stocks WHERE ticker = ? AND status=1', [$ticker]);
    }
}
