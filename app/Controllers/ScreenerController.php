<?php
declare(strict_types=1);

namespace App\Controllers;

class ScreenerController extends BaseController
{
    /** Preset screener definitions. */
    private function presets(): array
    {
        return [
            'stocks'    => ['title' => 'סקרינר מניות', 'desc' => 'סנן מניות לפי בורסה, מדינה, סקטור, שווי שוק, מכפיל, דיבידנד ועוד.', 'base' => 'stocks', 'where' => 's.status=1', 'bind' => []],
            'dividend'  => ['title' => 'סקרינר מניות דיבידנד', 'desc' => 'מניות עם תשואת דיבידנד גבוהה.', 'base' => 'stocks', 'where' => 's.status=1 AND s.dividend_yield >= 2', 'bind' => [], 'sort' => 'dividend_yield'],
            'ai'        => ['title' => 'סקרינר מניות בינה מלאכותית', 'desc' => 'מניות AI מובילות.', 'base' => 'theme', 'theme' => 'artificial-intelligence'],
            'robotics'  => ['title' => 'סקרינר מניות רובוטיקה', 'desc' => 'מניות רובוטיקה ואוטומציה.', 'base' => 'theme', 'theme' => 'robotics'],
            'hong-kong' => ['title' => 'סקרינר מניות הונג קונג', 'desc' => 'מניות הנסחרות בבורסת הונג קונג.', 'base' => 'hk', 'where' => "s.status=1", 'bind' => []],
            'growth'    => ['title' => 'סקרינר מניות צמיחה', 'desc' => 'מניות עם צמיחת הכנסות גבוהה.', 'base' => 'stocks', 'where' => 's.status=1 AND s.revenue_growth >= 15', 'bind' => [], 'sort' => 'revenue_growth'],
            'value'     => ['title' => 'סקרינר מניות ערך', 'desc' => 'מניות עם מכפילי תמחור נמוכים.', 'base' => 'stocks', 'where' => 's.status=1 AND s.pe_ratio > 0 AND s.pe_ratio <= 15', 'bind' => [], 'sort' => 'pe_ratio', 'dir' => 'ASC'],
        ];
    }

    public function index(array $params = []): void
    {
        $this->seo->set('סקרינרים - מנועי סינון מניות, ETF ואג"ח', 'מגוון סקרינרים פיננסיים: סקרינר מניות, ETF, דיבידנד, AI, רובוטיקה, הונג קונג, צמיחה, ערך, REIT, אג"ח וקריפטו.');
        $this->seo->addBreadcrumb('סקרינרים', SITE_URL . '/screener');
        $this->render('screener/list', ['presets' => $this->presets()]);
    }

    public function show(array $params): void
    {
        $type = $params['type'];
        $presets = $this->presets();
        $preset = $presets[$type] ?? $presets['stocks'];
        $this->seo->set($preset['title'], $preset['desc']);
        $this->seo->addBreadcrumb('סקרינרים', SITE_URL . '/screener');
        $this->seo->addBreadcrumb($preset['title'], SITE_URL . '/screener/' . $type);

        // filter options
        $sectors = $this->db->all('SELECT id, name, name_he FROM sectors WHERE status=1 ORDER BY name ASC');
        $exchanges = $this->db->all('SELECT id, name, name_he, code FROM exchanges WHERE status=1 ORDER BY name ASC');
        $countries = $this->db->all('SELECT id, name, name_he FROM countries WHERE status=1 ORDER BY name ASC');

        [$rows, $total, $pg] = $this->query($type, $preset);

        $this->render('screener/show', [
            'type' => $type, 'preset' => $preset, 'rows' => $rows, 'pagination' => $pg,
            'sectors' => $sectors, 'exchanges' => $exchanges, 'countries' => $countries,
            'baseUrl' => url('screener/' . $type),
        ]);
    }

    public function data(array $params): void
    {
        $type = $params['type'];
        $presets = $this->presets();
        $preset = $presets[$type] ?? $presets['stocks'];
        [$rows] = $this->query($type, $preset);
        $this->json(['rows' => $rows]);
    }

    private function query(string $type, array $preset): array
    {
        $where = 's.status=1';
        $bind = [];
        $joins = '';

        if (($preset['base'] ?? '') === 'theme') {
            $joins = 'JOIN stock_themes st ON s.id=st.stock_id JOIN themes t ON st.theme_id=t.id';
            $where .= ' AND t.slug = ?';
            $bind[] = $preset['theme'];
        } elseif (($preset['base'] ?? '') === 'hk') {
            $joins = 'JOIN exchanges ex ON s.exchange_id=ex.id';
            $where .= " AND ex.code IN ('HKEX','SEHK')";
        } elseif (!empty($preset['where'])) {
            $where = $preset['where'];
            $bind = $preset['bind'] ?? [];
        }

        // dynamic user filters
        if ($sec = request_param('sector')) { $where .= ' AND s.sector_id = ?'; $bind[] = (int) $sec; }
        if ($exc = request_param('exchange')) { $where .= ' AND s.exchange_id = ?'; $bind[] = (int) $exc; }
        if ($cty = request_param('country')) { $where .= ' AND s.country_id = ?'; $bind[] = (int) $cty; }
        if (($mc = request_param('mcap_min')) !== null) { $where .= ' AND s.market_cap >= ?'; $bind[] = (float) $mc * 1e9; }
        if (($mc = request_param('mcap_max')) !== null) { $where .= ' AND s.market_cap <= ?'; $bind[] = (float) $mc * 1e9; }
        if (($pe = request_param('pe_max')) !== null) { $where .= ' AND s.pe_ratio > 0 AND s.pe_ratio <= ?'; $bind[] = (float) $pe; }
        if (($dy = request_param('div_min')) !== null) { $where .= ' AND s.dividend_yield >= ?'; $bind[] = (float) $dy; }
        if (($pr = request_param('price_min')) !== null) { $where .= ' AND s.price >= ?'; $bind[] = (float) $pr; }
        if (($pr = request_param('price_max')) !== null) { $where .= ' AND s.price <= ?'; $bind[] = (float) $pr; }

        $sortable = ['market_cap', 'day_change_pct', 'volume', 'dividend_yield', 'pe_ratio', 'price', 'revenue_growth'];
        $sort = request_param('sort', $preset['sort'] ?? 'market_cap');
        if (!in_array($sort, $sortable, true)) $sort = 'market_cap';
        $dir = strtoupper((string) request_param('dir', $preset['dir'] ?? 'DESC'));
        $dir = $dir === 'ASC' ? 'ASC' : 'DESC';

        $page = $this->currentPage();
        $total = $this->db->count("SELECT COUNT(DISTINCT s.id) FROM stocks s $joins WHERE $where", $bind);
        $pg = $this->paginate($total, PER_PAGE_SCREENER, $page);

        $rows = $this->db->all(
            "SELECT DISTINCT s.id, s.ticker, s.company_name, s.company_name_he, s.slug, s.price, s.currency_code,
                    s.day_change_pct, s.market_cap, s.pe_ratio, s.dividend_yield, s.volume
             FROM stocks s $joins WHERE $where ORDER BY s.$sort $dir, s.id ASC
             LIMIT {$pg['perPage']} OFFSET {$pg['offset']}",
            $bind
        );

        return [$rows, $total, $pg];
    }
}
