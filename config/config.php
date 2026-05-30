<?php
/**
 * xbt.co.il - Application Configuration
 * Central configuration for the financial portal.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Environment
// ---------------------------------------------------------------------------
define('APP_ENV', getenv('APP_ENV') ?: 'production'); // 'production' | 'development'
define('APP_DEBUG', APP_ENV === 'development');

// ---------------------------------------------------------------------------
// Site identity
// ---------------------------------------------------------------------------
define('SITE_NAME', 'xbt.co.il');
define('SITE_TAGLINE', 'מאגר המידע הפיננסי המוביל בעברית על שוק ההון בארה"ב והונג קונג');
define('SITE_DOMAIN', 'xbt.co.il');

// Auto-detect base URL (works on localhost and production)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? SITE_DOMAIN;
define('SITE_URL', rtrim($scheme . '://' . $host, '/'));

define('SITE_LOCALE', 'he_IL');
define('SITE_LANG', 'he');
define('SITE_DIR', 'rtl');

// ---------------------------------------------------------------------------
// Paths
// ---------------------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('LOG_PATH', STORAGE_PATH . '/logs');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEW_PATH', APP_PATH . '/Views');

// ---------------------------------------------------------------------------
// Caching
// ---------------------------------------------------------------------------
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600);          // default cache lifetime (seconds)
define('CACHE_TTL_LIST', 600);      // list/index pages
define('CACHE_TTL_PAGE', 1800);     // detail pages

// ---------------------------------------------------------------------------
// Pagination
// ---------------------------------------------------------------------------
define('PER_PAGE', 50);
define('PER_PAGE_NEWS', 24);
define('PER_PAGE_SCREENER', 50);

// ---------------------------------------------------------------------------
// SEO defaults
// ---------------------------------------------------------------------------
define('SEO_TITLE_SUFFIX', ' | xbt.co.il');
define('SEO_DEFAULT_DESC', 'xbt.co.il - מאגר מידע פיננסי מעמיק בעברית: מניות, ETF, אג"ח, מדדים, קריפטו, סקטורים, מדריכים והשוואות על שוק ההון בארה"ב ובהונג קונג.');
define('SEO_TWITTER', '@xbt_co_il');

// ---------------------------------------------------------------------------
// Legal
// ---------------------------------------------------------------------------
define('DISCLAIMER_TEXT', 'המידע באתר נועד למטרות מידע ולימוד בלבד ואינו מהווה ייעוץ השקעות, שיווק השקעות, המלצה או תחליף לייעוץ מקצועי המותאם לצרכיו האישיים של כל אדם.');

// ---------------------------------------------------------------------------
// Error handling
// ---------------------------------------------------------------------------
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

// Ensure storage directories exist
foreach ([STORAGE_PATH, CACHE_PATH, LOG_PATH] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . '/php-error.log');

date_default_timezone_set('Asia/Jerusalem');
mb_internal_encoding('UTF-8');
