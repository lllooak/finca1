<?php
declare(strict_types=1);

/**
 * Content-only seeder. Populates authored Hebrew content (guides, glossary,
 * news, comparisons, FAQs) plus api_sources & settings WITHOUT touching the
 * real Marketstack market data already imported by database/import.php.
 *
 * USAGE:  MARKETSTACK_API_KEY=xxxx php database/seed_content.php
 */

if (PHP_SAPI !== 'cli' && (($_GET['run'] ?? '') !== '1')) {
    exit('Run "php database/seed_content.php" from CLI.');
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

@set_time_limit(0);
@ini_set('memory_limit', '512M');
$log = function (string $m) { echo $m . (PHP_SAPI === 'cli' ? "\n" : "<br>\n"); @ob_flush(); @flush(); };

$db = Database::getInstance();
$pdo = $db->pdo();
mt_srand(20240601);

// Shared helpers (mirror import.php)
function rnd(float $min, float $max, int $dec = 2): float { return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $dec); }
function rndi(int $min, int $max): int { return mt_rand($min, $max); }
function pick(array $a) { return $a[array_rand($a)]; }
function chance(int $pct): bool { return mt_rand(1, 100) <= $pct; }
function uslug(string $base, array &$used): string {
    $s = slugify($base); if ($s === '') { $s = 'item'; } $orig = $s; $i = 2;
    while (isset($used[$s])) { $s = $orig . '-' . $i++; }
    $used[$s] = true; return $s;
}
function batchInsert(PDO $pdo, string $table, array $cols, array $rows, int $chunk = 400): int {
    if (empty($rows)) return 0;
    $count = 0; $colList = '`' . implode('`,`', $cols) . '`';
    foreach (array_chunk($rows, $chunk) as $batch) {
        $ph = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = "INSERT INTO `$table` ($colList) VALUES " . implode(',', array_fill(0, count($batch), $ph));
        $flat = [];
        foreach ($batch as $r) { foreach ($cols as $c) { $flat[] = $r[$c] ?? null; } }
        $pdo->prepare($sql)->execute($flat);
        $count += count($batch);
    }
    return $count;
}

$log('=== Content seeder starting ===');

// Truncate only content/system tables (NOT market data)
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['guides','guide_categories','glossary_terms','comparisons','news','news_categories','faqs','api_sources','site_settings'] as $t) {
    $pdo->exec("TRUNCATE TABLE `$t`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
$log('Cleared content tables.');

require __DIR__ . '/seeders/content.php';

$log('=== Content seeding complete ===');
foreach (['guide_categories','guides','glossary_terms','news_categories','news','comparisons','faqs','api_sources','site_settings'] as $t) {
    $log(sprintf('  %-18s %s', $t, number_format((int) $db->value("SELECT COUNT(*) FROM `$t`"))));
}
