<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;

class IndexController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('מדדים - מדדי בורסה מובילים', 'מאגר מדדי בורסה: S&P 500, נאסד"ק, דאו ג\'ונס, האנג סנג ועוד. הרכב, שיטת שקלול, ביצועים וקרנות עוקבות.');
        $this->seo->addBreadcrumb('מדדים', SITE_URL . '/indices');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM indices WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all('SELECT name, name_he, slug, symbol, value, day_change_pct, ytd_return, return_1y, constituents_count FROM indices WHERE status=1 ORDER BY is_featured DESC, id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('index_asset/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('indices')]);
    }

    public function show(array $params): void
    {
        $ix = $this->db->one('SELECT i.*, c.name AS country_name, c.name_he AS country_name_he, c.slug AS country_slug FROM indices i LEFT JOIN countries c ON i.country_id=c.id WHERE i.slug = ? AND i.status=1', [$params['slug']]);
        if (!$ix) { $this->abort404(); }
        $name = $ix['name_he'] ?: $ix['name'];
        $this->seo->set("{$name} - מדד, הרכב, ביצועים וקרנות עוקבות", "ניתוח מדד {$name}: ערך נוכחי, הרכב המדד, החזקות עיקריות, שיטת שקלול, ביצועים היסטוריים וקרנות סל עוקבות.");
        $this->seo->addBreadcrumb('מדדים', SITE_URL . '/indices');
        $this->seo->addBreadcrumb($name, SITE_URL . '/index/' . $ix['slug']);

        $rel = Cache::remember('index:rel:' . $ix['id'], CACHE_TTL_PAGE, function () use ($ix) {
            return [
                'constituents' => $this->db->all('SELECT name, ticker, weight, stock_id FROM index_constituents WHERE index_id = ? ORDER BY weight DESC LIMIT 25', [$ix['id']]),
            ];
        });
        $faqs = $this->faqs('index', (int) $ix['id']);
        if (empty($faqs)) {
            $faqs = [
                ['question' => "כמה מניות יש במדד {$name}?", 'answer' => "המדד כולל כ-" . int_num($ix['constituents_count']) . " מניות."],
                ['question' => "מהי שיטת השקלול של {$name}?", 'answer' => "המדד משוקלל לפי " . ($ix['weighting_method'] ?: 'שווי שוק') . "."],
            ];
        }
        $this->seo->addFaqSchema($faqs);
        $this->render('index_asset/show', array_merge(['ix' => $ix, 'faqs' => $faqs], $rel));
    }
}
