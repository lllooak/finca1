<?php
declare(strict_types=1);

/**
 * CLI: clear the application cache.
 * Usage: php bin/cache-clear.php
 */
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Cache;

$count = Cache::flush();
echo "Cleared {$count} cache files.\n";
