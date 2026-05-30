<?php
declare(strict_types=1);

namespace App\Controllers;

class CryptoController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('קריפטו - מטבעות דיגיטליים', 'מאגר מטבעות קריפטו: ביטקוין, את\'ריום ועוד. מחיר, שווי שוק, היצע במחזור ונתונים נוספים.');
        $this->seo->addBreadcrumb('קריפטו', SITE_URL . '/crypto');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM cryptos WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all('SELECT name, name_he, slug, symbol, price, day_change_pct, market_cap, volume_24h, rank FROM cryptos WHERE status=1 ORDER BY market_cap DESC, id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('crypto/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('crypto')]);
    }

    public function show(array $params): void
    {
        $c = $this->db->one('SELECT * FROM cryptos WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$c) { $this->abort404(); }
        $name = $c['name_he'] ?: $c['name'];
        $this->seo->set("{$name} ({$c['symbol']}) - מחיר ונתוני קריפטו", "מחיר {$name} ({$c['symbol']}), שווי שוק, היצע במחזור, נפח מסחר ונתונים נוספים על המטבע הדיגיטלי.");
        $this->seo->addBreadcrumb('קריפטו', SITE_URL . '/crypto');
        $this->seo->addBreadcrumb($name, SITE_URL . '/crypto/' . $c['slug']);
        $faqs = $this->faqs('crypto', (int) $c['id']);
        if (empty($faqs)) {
            $faqs = [
                ['question' => "מהו המחיר של {$name}?", 'answer' => "המחיר הנוכחי הוא " . money($c['price'], 'USD', $c['price'] < 1 ? 4 : 2) . "."],
                ['question' => "מהו שווי השוק של {$name}?", 'answer' => "שווי השוק עומד על כ-" . big_number($c['market_cap'], 'USD') . "."],
            ];
        }
        $this->seo->addFaqSchema($faqs);
        $similar = $this->db->all('SELECT name, name_he, slug, symbol, price, day_change_pct, market_cap FROM cryptos WHERE id != ? AND status=1 ORDER BY market_cap DESC LIMIT 8', [$c['id']]);
        $this->render('crypto/show', ['c' => $c, 'faqs' => $faqs, 'similar' => $similar]);
    }
}
