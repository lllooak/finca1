<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;
use App\Core\View;

class SitemapController extends BaseController
{
    private array $sections = [
        'static', 'stocks', 'etfs', 'bonds', 'indices', 'reits', 'crypto',
        'commodities', 'currencies', 'sectors', 'themes', 'guides', 'glossary',
        'news', 'comparisons',
    ];

    public function index(array $params = []): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($this->sections as $s) {
            $out .= '  <sitemap><loc>' . SITE_URL . '/sitemap-' . $s . '.xml</loc><lastmod>' . date('Y-m-d') . '</lastmod></sitemap>' . "\n";
        }
        $out .= '</sitemapindex>';
        echo $out;
    }

    public function section(array $params): void
    {
        $type = $params['type'];
        header('Content-Type: application/xml; charset=utf-8');
        $urls = Cache::remember('sitemap:' . $type, 3600, fn() => $this->urlsFor($type));

        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $out .= '  <url><loc>' . e($u['loc']) . '</loc>';
            if (!empty($u['lastmod'])) $out .= '<lastmod>' . e($u['lastmod']) . '</lastmod>';
            $out .= '<changefreq>' . ($u['freq'] ?? 'weekly') . '</changefreq>';
            $out .= '<priority>' . ($u['priority'] ?? '0.6') . '</priority></url>' . "\n";
        }
        $out .= '</urlset>';
        echo $out;
    }

    private function urlsFor(string $type): array
    {
        $map = [
            'stocks'      => ['stocks', 'ticker', 'stock/', 'updated_at'],
            'etfs'        => ['etfs', 'ticker', 'etf/', 'updated_at'],
            'reits'       => ['reits', 'ticker', 'reit/', 'updated_at'],
            'bonds'       => ['bonds', 'slug', 'bond/', 'updated_at'],
            'indices'     => ['indices', 'slug', 'index/', 'updated_at'],
            'crypto'      => ['cryptos', 'slug', 'crypto/', 'updated_at'],
            'commodities' => ['commodities', 'slug', 'commodity/', 'updated_at'],
            'currencies'  => ['currencies', 'slug', 'currency/', 'updated_at'],
            'sectors'     => ['sectors', 'slug', 'sector/', 'updated_at'],
            'themes'      => ['themes', 'slug', 'theme/', 'updated_at'],
            'guides'      => ['guides', 'slug', 'guide/', 'updated_at'],
            'glossary'    => ['glossary_terms', 'slug', 'glossary/', 'updated_at'],
            'news'        => ['news', 'slug', 'news/', 'updated_at'],
            'comparisons' => ['comparisons', 'slug', 'compare/', 'updated_at'],
        ];

        if ($type === 'static') {
            $paths = ['', 'stocks', 'etfs', 'bonds', 'indices', 'reits', 'crypto', 'commodities', 'currencies', 'sectors', 'industries', 'themes', 'countries', 'exchanges', 'screener', 'compare', 'guides', 'glossary', 'news', 'markets', 'about', 'disclaimer', 'contact', 'faq', 'terms', 'privacy'];
            return array_map(fn($p) => ['loc' => url($p), 'freq' => 'daily', 'priority' => $p === '' ? '1.0' : '0.8'], $paths);
        }

        if (!isset($map[$type])) { return []; }
        [$table, $col, $route, $mod] = $map[$type];
        $rows = $this->db->all("SELECT $col AS k, $mod AS m FROM $table WHERE status=1 LIMIT 50000");
        return array_map(fn($r) => [
            'loc' => url($route . $r['k']),
            'lastmod' => $r['m'] ? date('Y-m-d', strtotime((string) $r['m'])) : null,
            'freq' => 'weekly',
            'priority' => '0.6',
        ], $rows);
    }

    public function html(array $params = []): void
    {
        $this->seo->set('מפת אתר', 'מפת האתר של xbt.co.il - כל הקטגוריות והדפים המרכזיים.');
        $this->seo->addBreadcrumb('מפת אתר', SITE_URL . '/sitemap');
        $data = [
            'sectors' => $this->db->all('SELECT name, name_he, slug FROM sectors WHERE status=1 ORDER BY name ASC LIMIT 60'),
            'themes'  => $this->db->all('SELECT name, name_he, slug FROM themes WHERE status=1 ORDER BY name ASC LIMIT 60'),
            'guides'  => $this->db->all('SELECT title, slug FROM guides WHERE status=1 ORDER BY views DESC LIMIT 40'),
        ];
        $this->render('page/sitemap', $data);
    }
}
