<?php
declare(strict_types=1);

namespace App\Controllers;

class BondController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('אג"ח - מאגר איגרות חוב', 'מאגר איגרות חוב: אג"ח ממשלתיות וקונצרניות, תשואות, מח"מ, דירוג אשראי ורמות סיכון.');
        $this->seo->addBreadcrumb('אג"ח', SITE_URL . '/bonds');

        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM bonds WHERE status=1');
        $pg = $this->paginate($total, PER_PAGE, $page);
        $rows = $this->db->all('SELECT name, name_he, slug, issuer, bond_type, yield, coupon, duration, credit_rating, risk_level, maturity_years, currency_code FROM bonds WHERE status=1 ORDER BY yield DESC, id ASC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('bond/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('bonds')]);
    }

    public function show(array $params): void
    {
        $b = $this->db->one('SELECT b.*, c.name AS country_name, c.name_he AS country_name_he, c.slug AS country_slug FROM bonds b LEFT JOIN countries c ON b.country_id=c.id WHERE b.slug = ? AND b.status=1', [$params['slug']]);
        if (!$b) { $this->abort404(); }
        $name = $b['name_he'] ?: $b['name'];
        $this->seo->set("{$name} - אג\"ח, תשואה, מח\"מ ודירוג", "ניתוח אג\"ח {$name}: תשואה לפדיון, מח\"מ, דירוג אשראי, סיכון ריבית וסיכון אינפלציה.");
        $this->seo->addBreadcrumb('אג"ח', SITE_URL . '/bonds');
        $this->seo->addBreadcrumb($name, SITE_URL . '/bond/' . $b['slug']);

        $faqs = $this->faqs('bond', (int) $b['id']);
        if (empty($faqs)) {
            $faqs = [
                ['question' => "מהי התשואה של {$name}?", 'answer' => "התשואה הנוכחית עומדת על " . num($b['yield']) . "%."],
                ['question' => "מהו דירוג האשראי של האג\"ח?", 'answer' => "דירוג האשראי הוא {$b['credit_rating']}, המשקף רמת סיכון " . ($b['risk_level'] ?: 'בינונית') . "."],
                ['question' => "מהו המח\"מ של האג\"ח?", 'answer' => "המח\"מ (Duration) עומד על " . num($b['duration']) . " שנים."],
            ];
        }
        $this->seo->addFaqSchema($faqs);
        $similar = $this->db->all('SELECT name, name_he, slug, yield, credit_rating, maturity_years FROM bonds WHERE id != ? AND bond_type <=> ? AND status=1 ORDER BY yield DESC LIMIT 8', [$b['id'], $b['bond_type']]);
        $this->render('bond/show', ['b' => $b, 'faqs' => $faqs, 'similar' => $similar]);
    }
}
