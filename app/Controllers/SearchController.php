<?php
declare(strict_types=1);

namespace App\Controllers;

class SearchController extends BaseController
{
    public function index(array $params = []): void
    {
        $q = trim((string) request_param('q', ''));
        $this->seo->set('חיפוש: ' . $q, 'תוצאות חיפוש באתר xbt.co.il עבור: ' . $q);
        $this->seo->robots = 'noindex, follow';
        $this->seo->addBreadcrumb('חיפוש', SITE_URL . '/search');

        $results = [];
        if (mb_strlen($q) >= 2) {
            $results = $this->searchAll($q, 100);
        }
        $this->render('search/index', ['q' => $q, 'results' => $results]);
    }

    public function autocomplete(array $params = []): void
    {
        $q = trim((string) request_param('q', ''));
        if (mb_strlen($q) < 2) {
            $this->json(['results' => []]);
        }
        $this->json(['results' => $this->searchAll($q, 12)]);
    }

    private function searchAll(string $q, int $limit): array
    {
        $like = '%' . $q . '%';
        $out = [];

        $stocks = $this->db->all(
            "SELECT ticker, company_name, company_name_he, slug FROM stocks
             WHERE status=1 AND (ticker LIKE ? OR company_name LIKE ? OR company_name_he LIKE ?)
             ORDER BY (ticker = ?) DESC, market_cap DESC LIMIT ?",
            [$like, $like, $like, strtoupper($q), $limit]
        );
        foreach ($stocks as $s) {
            $out[] = ['type' => 'מניה', 'label' => $s['company_name_he'] ?: $s['company_name'], 'ticker' => $s['ticker'], 'url' => url('stock/' . $s['ticker'])];
        }

        $remaining = max(2, $limit - count($out));
        $etfs = $this->db->all(
            "SELECT ticker, name, name_he FROM etfs WHERE status=1 AND (ticker LIKE ? OR name LIKE ? OR name_he LIKE ?) LIMIT ?",
            [$like, $like, $like, $remaining]
        );
        foreach ($etfs as $e) {
            $out[] = ['type' => 'ETF', 'label' => $e['name_he'] ?: $e['name'], 'ticker' => $e['ticker'], 'url' => url('etf/' . $e['ticker'])];
        }

        if (count($out) < $limit) {
            $rem = $limit - count($out);
            $more = [
                ['t' => 'מדד', 'sql' => "SELECT name,name_he,slug FROM indices WHERE status=1 AND (name LIKE ? OR name_he LIKE ?) LIMIT ?", 'route' => 'index/'],
                ['t' => 'קריפטו', 'sql' => "SELECT name,name_he,slug FROM cryptos WHERE status=1 AND (name LIKE ? OR symbol LIKE ?) LIMIT ?", 'route' => 'crypto/'],
                ['t' => 'מונח', 'sql' => "SELECT term AS name, term_he AS name_he, slug FROM glossary_terms WHERE status=1 AND (term LIKE ? OR term_he LIKE ?) LIMIT ?", 'route' => 'glossary/'],
                ['t' => 'מדריך', 'sql' => "SELECT title AS name, title AS name_he, slug FROM guides WHERE status=1 AND title LIKE ? AND title LIKE ? LIMIT ?", 'route' => 'guide/'],
            ];
            foreach ($more as $m) {
                if (count($out) >= $limit) break;
                $rows = $this->db->all($m['sql'], [$like, $like, $rem]);
                foreach ($rows as $r) {
                    $out[] = ['type' => $m['t'], 'label' => $r['name_he'] ?: $r['name'], 'ticker' => '', 'url' => url($m['route'] . $r['slug'])];
                }
            }
        }

        return array_slice($out, 0, $limit);
    }
}
