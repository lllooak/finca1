<?php
declare(strict_types=1);

/**
 * One-off repair: regenerate varied summary/content/tags for existing news rows.
 *
 * Use this when the database was already imported with the old seed data, where
 * every article shared the same placeholder body. Run from the project root:
 *
 *     php database/fix_news_content.php
 *
 * It reads DB credentials from config/database.php (same as the app) and updates
 * every row in `news` in place. Safe to re-run (output is deterministic per id).
 */

$root = dirname(__DIR__);
require_once $root . '/database/news_generator.php';

$cfg = require $root . '/config/database.php';

$dsn = sprintf(
    '%s:host=%s;port=%s;dbname=%s;charset=%s',
    $cfg['driver'], $cfg['host'], $cfg['port'], $cfg['database'], $cfg['charset']
);

try {
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options'] ?? []);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

$cats = [];
foreach ($pdo->query('SELECT id, name_he FROM news_categories')->fetchAll() as $c) {
    $cats[(int)$c['id']] = (string)$c['name_he'];
}
$catNames = array_values(array_unique(array_values($cats)));

$rows = $pdo->query('SELECT id, title, category_id FROM news ORDER BY id')->fetchAll();
$total = count($rows);
echo "Found {$total} news rows. Regenerating content...\n";

$update = $pdo->prepare('UPDATE news SET summary = :summary, content = :content, tags = :tags WHERE id = :id');

$done = 0;
$pdo->beginTransaction();
foreach ($rows as $row) {
    $id    = (int)$row['id'];
    $title = (string)$row['title'];
    $catHe = $cats[(int)$row['category_id']] ?? '';

    $article = generate_news_article($title, $catHe, $id);

    // Vary tags too: own category first, then a couple of distinct others.
    $names = $catNames;
    shuffle($names);
    $tags = $catHe !== '' ? [$catHe] : [];
    foreach ($names as $nm) {
        if ($nm !== $catHe) { $tags[] = $nm; }
        if (count($tags) >= 3) { break; }
    }

    $update->execute([
        ':summary' => $article['summary'],
        ':content' => $article['content'],
        ':tags'    => implode(',', $tags),
        ':id'      => $id,
    ]);
    $done++;
}
$pdo->commit();

echo "Updated {$done} news rows.\n";
