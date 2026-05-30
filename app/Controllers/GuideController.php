<?php
declare(strict_types=1);

namespace App\Controllers;

class GuideController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('מדריכים פיננסיים - מדריכי השקעות בעברית', 'מאגר מדריכי השקעות בעברית: מניות, ETF, אג"ח, דיבידנדים, השקעות בסין והונג קונג, בינה מלאכותית ועוד.');
        $this->seo->addBreadcrumb('מדריכים', SITE_URL . '/guides');
        $cats = $this->db->all('SELECT name, name_he, slug FROM guide_categories WHERE status=1 ORDER BY id ASC');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM guides WHERE status=1');
        $pg = $this->paginate($total, 24, $page);
        $rows = $this->db->all('SELECT g.title, g.slug, g.summary, g.reading_time, g.level, c.name_he AS cat_he, c.name AS cat, c.slug AS cat_slug FROM guides g LEFT JOIN guide_categories c ON g.category_id=c.id WHERE g.status=1 ORDER BY g.is_featured DESC, g.id DESC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset']);
        $this->render('guide/index', ['rows' => $rows, 'cats' => $cats, 'pagination' => $pg, 'baseUrl' => url('guides'), 'title' => 'מדריכים פיננסיים']);
    }

    public function byCategory(array $params): void
    {
        $cat = $this->db->one('SELECT * FROM guide_categories WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$cat) { $this->abort404(); }
        $name = $cat['name_he'] ?: $cat['name'];
        $this->seo->set("מדריכים: {$name}", "מדריכי השקעות בנושא {$name}.");
        $this->seo->addBreadcrumb('מדריכים', SITE_URL . '/guides');
        $this->seo->addBreadcrumb($name, SITE_URL . '/guides/category/' . $cat['slug']);
        $cats = $this->db->all('SELECT name, name_he, slug FROM guide_categories WHERE status=1 ORDER BY id ASC');
        $page = $this->currentPage();
        $total = $this->db->count('SELECT COUNT(*) FROM guides WHERE status=1 AND category_id = ?', [$cat['id']]);
        $pg = $this->paginate($total, 24, $page);
        $rows = $this->db->all('SELECT title, slug, summary, reading_time, level FROM guides WHERE status=1 AND category_id = ? ORDER BY id DESC LIMIT ' . $pg['perPage'] . ' OFFSET ' . $pg['offset'], [$cat['id']]);
        $this->render('guide/index', ['rows' => $rows, 'cats' => $cats, 'pagination' => $pg, 'baseUrl' => url('guides/category/' . $cat['slug']), 'title' => "מדריכים: {$name}"]);
    }

    public function show(array $params): void
    {
        $g = $this->db->one('SELECT g.*, c.name_he AS cat_he, c.name AS cat, c.slug AS cat_slug FROM guides g LEFT JOIN guide_categories c ON g.category_id=c.id WHERE g.slug = ? AND g.status=1', [$params['slug']]);
        if (!$g) { $this->abort404(); }
        $this->db->run('UPDATE guides SET views = views + 1 WHERE id = ?', [$g['id']]);
        $this->seo->set($g['meta_title'] ?: $g['title'], $g['meta_desc'] ?: excerpt($g['summary'], 200));
        $this->seo->ogType = 'article';
        $this->seo->addBreadcrumb('מדריכים', SITE_URL . '/guides');
        if ($g['cat_slug']) $this->seo->addBreadcrumb($g['cat_he'] ?: $g['cat'], SITE_URL . '/guides/category/' . $g['cat_slug']);
        $this->seo->addBreadcrumb($g['title'], SITE_URL . '/guide/' . $g['slug']);
        $this->seo->addArticleSchema(['title' => $g['title'], 'description' => excerpt($g['summary'], 200), 'published' => $g['published_at'] ?: $g['created_at'], 'modified' => $g['updated_at']]);
        $faqs = $this->faqs('guide', (int) $g['id']);
        $this->seo->addFaqSchema($faqs);
        $related = $this->db->all('SELECT title, slug, summary FROM guides WHERE id != ? AND category_id <=> ? AND status=1 ORDER BY views DESC LIMIT 6', [$g['id'], $g['category_id']]);
        $this->render('guide/show', ['g' => $g, 'faqs' => $faqs, 'related' => $related]);
    }
}
