<?php
declare(strict_types=1);

namespace App\Controllers;

class GlossaryController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('מילון מונחים פיננסי - מונחי בורסה והשקעות', 'מילון מונחים פיננסי מקיף בעברית: P/E, EPS, EBITDA, ROE, תזרים מזומנים, שווי שוק ומאות מונחים נוספים עם הסבר ודוגמאות.');
        $this->seo->addBreadcrumb('מילון מונחים', SITE_URL . '/glossary');
        $letter = request_param('letter');
        $page = $this->currentPage();
        $where = 'WHERE status=1';
        $bind = [];
        if ($letter) { $where .= ' AND letter = ?'; $bind[] = strtoupper((string)$letter); }
        $total = $this->db->count("SELECT COUNT(*) FROM glossary_terms $where", $bind);
        $pg = $this->paginate($total, 60, $page);
        $rows = $this->db->all("SELECT term, term_he, slug, definition, category FROM glossary_terms $where ORDER BY term ASC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}", $bind);
        $letters = range('A', 'Z');
        $this->render('glossary/index', ['rows' => $rows, 'pagination' => $pg, 'baseUrl' => url('glossary'), 'letters' => $letters, 'active' => $letter]);
    }

    public function show(array $params): void
    {
        $t = $this->db->one('SELECT * FROM glossary_terms WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$t) { $this->abort404(); }
        $this->db->run('UPDATE glossary_terms SET views = views + 1 WHERE id = ?', [$t['id']]);
        $term = $t['term_he'] ?: $t['term'];
        $this->seo->set($t['meta_title'] ?: "{$term} - הגדרה והסבר", $t['meta_desc'] ?: excerpt($t['definition'], 200));
        $this->seo->addBreadcrumb('מילון מונחים', SITE_URL . '/glossary');
        $this->seo->addBreadcrumb($term, SITE_URL . '/glossary/' . $t['slug']);
        $faqs = $this->faqs('glossary', (int) $t['id']);
        $this->seo->addFaqSchema($faqs);
        $related = $this->db->all('SELECT term, term_he, slug FROM glossary_terms WHERE id != ? AND category <=> ? AND status=1 ORDER BY views DESC LIMIT 10', [$t['id'], $t['category']]);
        $this->render('glossary/show', ['t' => $t, 'term' => $term, 'faqs' => $faqs, 'related' => $related]);
    }
}
