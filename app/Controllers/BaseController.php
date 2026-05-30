<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Seo;
use App\Core\Database;

abstract class BaseController
{
    protected Database $db;
    protected Seo $seo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->seo = new Seo();
        $this->seo->addOrganizationSchema();
        $this->seo->addBreadcrumb('בית', SITE_URL . '/');
    }

    protected function render(string $template, array $data = []): void
    {
        $data['seo'] = $this->seo;
        View::render($template, $data);
    }

    protected function paginate(int $total, int $perPage, int $currentPage): array
    {
        $pages = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($currentPage, $pages));
        $offset = ($currentPage - 1) * $perPage;
        return [
            'total'   => $total,
            'pages'   => $pages,
            'current' => $currentPage,
            'offset'  => $offset,
            'perPage' => $perPage,
            'hasPrev' => $currentPage > 1,
            'hasNext' => $currentPage < $pages,
        ];
    }

    protected function currentPage(): int
    {
        return max(1, (int) request_param('page', 1));
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function abort404(): void
    {
        http_response_code(404);
        (new ErrorController())->notFound();
        exit;
    }

    /** Load FAQs for an entity. */
    protected function faqs(string $type, ?int $id = null): array
    {
        return $this->db->all(
            'SELECT question, answer FROM faqs WHERE entity_type = ? AND (entity_id = ? OR entity_id IS NULL) AND status = 1 ORDER BY position ASC LIMIT 12',
            [$type, $id]
        );
    }
}
