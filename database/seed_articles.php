<?php
declare(strict_types=1);

/**
 * Populates 100 real Hebrew stock articles into the stocks table.
 * USAGE: php database/seed_articles.php
 */
if (PHP_SAPI !== 'cli') exit('CLI only');
require dirname(__DIR__) . '/app/bootstrap.php';
use App\Core\Database;
@set_time_limit(0);

$db  = Database::getInstance();
$pdo = $db->pdo();
$log = fn(string $m) => print($m . "\n");

$all = array_merge(
    require __DIR__ . '/seeders/stock_articles_1.php',
    require __DIR__ . '/seeders/stock_articles_2.php',
    require __DIR__ . '/seeders/stock_articles_3.php',
    require __DIR__ . '/seeders/stock_articles_4.php',
    require __DIR__ . '/seeders/stock_articles_5.php'
);

$log('Seeding ' . count($all) . ' stock articles...');

$st = $pdo->prepare(
    'UPDATE stocks SET
        description_he       = ?,
        business_model       = ?,
        products_services    = ?,
        revenue_sources      = ?,
        competitive_advantages = ?,
        growth_drivers       = ?,
        risks                = ?,
        updated_at           = NOW()
     WHERE ticker = ?'
);

$done = 0; $miss = 0;
foreach ($all as $ticker => $a) {
    $st->execute([$a['d'],$a['b'],$a['p'],$a['r'],$a['c'],$a['g'],$a['k'],$ticker]);
    if ($st->rowCount() > 0) { $done++; } else { $miss++; }
}

$log("Updated: {$done}  Not found in DB: {$miss}");
$log('Done. Clear the cache: php bin/cache-clear.php');
