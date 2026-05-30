<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Seo;

class ErrorController
{
    public function notFound(): void
    {
        http_response_code(404);
        $seo = new Seo();
        $seo->set('הדף לא נמצא (404)');
        $seo->robots = 'noindex, follow';
        View::render('errors/404', ['seo' => $seo]);
    }

    public function serverError(): void
    {
        http_response_code(500);
        $seo = new Seo();
        $seo->set('שגיאת שרת (500)');
        $seo->robots = 'noindex, nofollow';
        View::render('errors/500', ['seo' => $seo]);
    }
}
