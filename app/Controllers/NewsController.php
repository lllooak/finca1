<?php
declare(strict_types=1);

namespace App\Controllers;

class NewsController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set('חדשות פיננסיות - חדשות שוק ההון בארה"ב והונג קונג', 'חדשות פיננסיות עדכניות: שוק המניות בארה"ב, הונג קונג, בינה מלאכותית, מוליכים למחצה, קריפטו, פד וכלכלה.');
        $this->seo->addBreadcrumb('חדשות', SITE_URL . '/news');
        $this->listing('WHERE n.status=1', [], url('news'), 'חדשות פיננסיות');
    }

    public function byCategory(array $params): void
    {
        $cat = $this->db->one('SELECT * FROM news_categories WHERE slug = ? AND status=1', [$params['slug']]);
        if (!$cat) { $this->abort404(); }
        $name = $cat['name_he'] ?: $cat['name'];
        $this->seo->set("חדשות: {$name}", "חדשות עדכניות בנושא {$name}.");
        $this->seo->addBreadcrumb('חדשות', SITE_URL . '/news');
        $this->seo->addBreadcrumb($name, SITE_URL . '/news/category/' . $cat['slug']);
        $this->listing('WHERE n.status=1 AND n.category_id = ?', [$cat['id']], url('news/category/' . $cat['slug']), "חדשות: {$name}");
    }

    private function listing(string $where, array $bind, string $baseUrl, string $title): void
    {
        $cats = $this->db->all('SELECT name, name_he, slug FROM news_categories WHERE status=1 ORDER BY id ASC');
        $page = $this->currentPage();
        $total = $this->db->count("SELECT COUNT(*) FROM news n $where", $bind);
        $pg = $this->paginate($total, PER_PAGE_NEWS, $page);
        $rows = $this->db->all("SELECT n.title, n.slug, n.summary, n.image_url, n.published_at, c.name_he AS cat_he, c.name AS cat FROM news n LEFT JOIN news_categories c ON n.category_id=c.id $where ORDER BY n.published_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}", $bind);
        $this->render('news/index', ['rows' => $rows, 'cats' => $cats, 'pagination' => $pg, 'baseUrl' => $baseUrl, 'title' => $title]);
    }

    public function show(array $params): void
    {
        $n = $this->db->one('SELECT n.*, c.name_he AS cat_he, c.name AS cat, c.slug AS cat_slug FROM news n LEFT JOIN news_categories c ON n.category_id=c.id WHERE n.slug = ? AND n.status=1', [$params['slug']]);
        if (!$n) { $this->abort404(); }
        $this->db->run('UPDATE news SET views = views + 1 WHERE id = ?', [$n['id']]);
        $this->seo->set($n['meta_title'] ?: $n['title'], $n['meta_desc'] ?: excerpt($n['summary'], 200));
        $this->seo->ogType = 'article';
        $this->seo->addBreadcrumb('חדשות', SITE_URL . '/news');
        if ($n['cat_slug']) $this->seo->addBreadcrumb($n['cat_he'] ?: $n['cat'], SITE_URL . '/news/category/' . $n['cat_slug']);
        $this->seo->addBreadcrumb($n['title'], SITE_URL . '/news/' . $n['slug']);
        $this->seo->addArticleSchema(['title' => $n['title'], 'description' => excerpt($n['summary'], 200), 'published' => $n['published_at'] ?: $n['created_at'], 'modified' => $n['updated_at']]);
        $related = $this->db->all('SELECT title, slug, summary, published_at FROM news WHERE id != ? AND category_id <=> ? AND status=1 ORDER BY published_at DESC LIMIT 6', [$n['id'], $n['category_id']]);
        $this->render('news/show', ['n' => $n, 'related' => $related]);
    }
}
