<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;

class CategoryController extends BaseController
{
    // ---- listing pages ----
    public function sectors(array $params = []): void
    {
        $this->seo->set('סקטורים - כל סקטורי השוק', 'כל הסקטורים בשוק ההון: טכנולוגיה, פיננסים, בריאות, אנרגיה ועוד. מניות, ניתוח ומידע לכל סקטור.');
        $this->seo->addBreadcrumb('סקטורים', SITE_URL . '/sectors');
        $rows = $this->db->all('SELECT name, name_he, slug, icon, description FROM sectors WHERE status=1 ORDER BY name_he ASC, name ASC');
        $this->render('category/list', ['title' => 'סקטורים', 'rows' => $rows, 'route' => 'sector/', 'icon' => 'bi-grid-3x3-gap']);
    }

    public function industries(array $params = []): void
    {
        $this->seo->set('תעשיות - כל התעשיות בשוק', 'מאגר תעשיות בשוק ההון עם מניות וניתוח לכל תעשייה.');
        $this->seo->addBreadcrumb('תעשיות', SITE_URL . '/industries');
        $rows = $this->db->all('SELECT name, name_he, slug, description FROM industries WHERE status=1 ORDER BY name ASC LIMIT 500');
        $this->render('category/list', ['title' => 'תעשיות', 'rows' => $rows, 'route' => 'industry/', 'icon' => 'bi-diagram-3']);
    }

    public function themes(array $params = []): void
    {
        $this->seo->set('Themes השקעה - מגמות ונושאי השקעה', 'נושאי השקעה (Themes): בינה מלאכותית, רובוטיקה, מוליכים למחצה, סייבר, אנרגיה נקייה ועוד.');
        $this->seo->addBreadcrumb('Themes', SITE_URL . '/themes');
        $rows = $this->db->all('SELECT name, name_he, slug, icon, category, description FROM themes WHERE status=1 ORDER BY is_featured DESC, name ASC');
        $this->render('category/list', ['title' => 'Themes השקעה', 'rows' => $rows, 'route' => 'theme/', 'icon' => 'bi-stars']);
    }

    public function countries(array $params = []): void
    {
        $this->seo->set('מדינות - שווקים גלובליים', 'מאגר מדינות ושווקים: ארה"ב, סין, הונג קונג ועוד עם מניות ובורסות.');
        $this->seo->addBreadcrumb('מדינות', SITE_URL . '/countries');
        $rows = $this->db->all('SELECT name, name_he, slug, flag_emoji AS icon_text, description FROM countries WHERE status=1 ORDER BY name ASC');
        $this->render('category/list', ['title' => 'מדינות', 'rows' => $rows, 'route' => 'country/', 'icon' => 'bi-globe']);
    }

    public function exchanges(array $params = []): void
    {
        $this->seo->set('בורסות - בורסות מסחר עולמיות', 'מאגר בורסות מסחר: NYSE, NASDAQ, HKEX ועוד עם המניות הנסחרות בכל אחת.');
        $this->seo->addBreadcrumb('בורסות', SITE_URL . '/exchanges');
        $rows = $this->db->all('SELECT name, name_he, slug, code, description FROM exchanges WHERE status=1 ORDER BY name ASC');
        $this->render('category/list', ['title' => 'בורסות', 'rows' => $rows, 'route' => 'exchange/', 'icon' => 'bi-building']);
    }

    // ---- detail pages ----
    public function sector(array $params): void
    {
        $row = $this->db->one('SELECT * FROM sectors WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$row) { $this->abort404(); }
        $this->detail($row, 'sector', 'sector_id', 'סקטור');
    }

    public function industry(array $params): void
    {
        $row = $this->db->one('SELECT * FROM industries WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$row) { $this->abort404(); }
        $this->detail($row, 'industry', 'industry_id', 'תעשייה');
    }

    public function theme(array $params): void
    {
        $row = $this->db->one('SELECT * FROM themes WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$row) { $this->abort404(); }
        $name = $row['name_he'] ?: $row['name'];
        $this->seo->set("מניות {$name} - " . ($row['category'] ?: 'Theme'), ($row['description'] ?: "כל המניות והקרנות בנושא {$name}.") . " ניתוח, רשימת מניות וקרנות סל קשורות.");
        $this->seo->addBreadcrumb('Themes', SITE_URL . '/themes');
        $this->seo->addBreadcrumb($name, SITE_URL . '/theme/' . $row['slug']);
        $stocks = $this->db->all('SELECT s.ticker, s.company_name, s.company_name_he, s.slug, s.price, s.currency_code, s.day_change_pct, s.market_cap, s.pe_ratio, s.dividend_yield, s.volume FROM stocks s JOIN stock_themes st ON s.id=st.stock_id WHERE st.theme_id = ? AND s.status=1 ORDER BY s.market_cap DESC LIMIT 100', [$row['id']]);
        $etfs = $this->db->all('SELECT e.ticker, e.name, e.name_he, e.slug FROM etfs e JOIN etf_themes et ON e.id=et.etf_id WHERE et.theme_id = ? AND e.status=1 LIMIT 12', [$row['id']]);
        $faqs = $this->faqs('theme', (int) $row['id']);
        $this->seo->addFaqSchema($faqs);
        $this->render('category/detail', ['row' => $row, 'name' => $name, 'kind' => 'theme', 'stocks' => $stocks, 'etfs' => $etfs, 'faqs' => $faqs]);
    }

    public function country(array $params): void
    {
        $row = $this->db->one('SELECT * FROM countries WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$row) { $this->abort404(); }
        $this->detail($row, 'country', 'country_id', 'מדינה');
    }

    public function exchange(array $params): void
    {
        $row = $this->db->one('SELECT * FROM exchanges WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$row) { $this->abort404(); }
        $this->detail($row, 'exchange', 'exchange_id', 'בורסה');
    }

    private function detail(array $row, string $kind, string $column, string $label): void
    {
        $name = $row['name_he'] ?: $row['name'];
        $this->seo->set("מניות {$label} {$name} - ניתוח ורשימת מניות", ($row['description'] ?? '') ?: "כל המניות והמידע על {$label} {$name}: רשימת מניות, ביצועים וניתוח מעמיק.");
        $this->seo->addBreadcrumb($label, SITE_URL . '/' . $kind . 's');
        $this->seo->addBreadcrumb($name, SITE_URL . '/' . $kind . '/' . $row['slug']);
        $stocks = $this->db->all(
            "SELECT ticker, company_name, company_name_he, slug, price, currency_code, day_change_pct, market_cap, pe_ratio, dividend_yield, volume FROM stocks WHERE $column = ? AND status=1 ORDER BY market_cap DESC LIMIT 100",
            [$row['id']]
        );
        $faqs = $this->faqs($kind, (int) $row['id']);
        $this->seo->addFaqSchema($faqs);
        $this->render('category/detail', ['row' => $row, 'name' => $name, 'kind' => $kind, 'stocks' => $stocks, 'etfs' => [], 'faqs' => $faqs]);
    }
}
