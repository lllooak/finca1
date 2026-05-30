<?php
declare(strict_types=1);

/**
 * xbt.co.il - Front Controller
 * All requests are routed through this single entry point.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;
use App\Core\View;
use App\Core\Seo;

// Share global view data
View::share('siteName', SITE_NAME);
View::share('siteUrl', SITE_URL);

$router = new Router();
require APP_PATH . '/routes.php';

try {
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/'
    );
} catch (\Throwable $e) {
    error_log('[FATAL] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (APP_DEBUG) {
        http_response_code(500);
        echo '<pre style="direction:ltr;text-align:left;padding:20px">';
        echo e($e->getMessage()) . "\n\n";
        echo e($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        $controller = new \App\Controllers\ErrorController();
        $controller->serverError();
    }
}
