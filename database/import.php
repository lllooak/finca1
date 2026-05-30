<?php
declare(strict_types=1);

/**
 * xbt.co.il - Marketstack Real-Data Importer
 * =====================================================================
 * Replaces the synthetic seeder. Pulls REAL market data from the
 * Marketstack v2 API (Basic plan, 10,000 req/mo) and stores it locally.
 *
 *   - Real exchanges (2,800+), currencies, derived countries
 *   - Real stocks (US + HK) with latest EOD price, real day-change,
 *     ~10 days price history, and (for a subset) company info,
 *     1-year history, dividends and splits.
 *   - Real ETFs (latest price), real indices (value + returns).
 *   - Authored Hebrew content (guides, glossary, news, comparisons).
 *
 * No real-time quotes. No fabricated market numbers - unknown fields
 * are left NULL.
 *
 * USAGE:  MARKETSTACK_API_KEY=xxxx php database/import.php
 * =====================================================================
 */

if (PHP_SAPI !== 'cli' && (($_GET['run'] ?? '') !== '1')) {
    exit('Run "php database/import.php" from CLI (or add ?run=1 in dev).');
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

@set_time_limit(0);
@ini_set('memory_limit', '1024M');
$start = microtime(true);
$log = function (string $m) { echo $m . (PHP_SAPI === 'cli' ? "\n" : "<br>\n"); @ob_flush(); @flush(); };

$db = Database::getInstance();
$pdo = $db->pdo();
mt_srand(20240601);

// ---------------------------------------------------------------------
// Tunable caps (kept comfortably within 10,000 requests/month)
// ---------------------------------------------------------------------
const STOCK_EXCHANGES = ['XNAS' => 'US', 'XNYS' => 'US', 'XHKG' => 'HK']; // MIC => country iso2
const STOCK_CAP        = 3000;   // total real stocks to import
const PRICE_BATCH      = 100;    // symbols per eod request (API max)
const INFO_SUBSET      = 600;    // tickerinfo calls (real sector/industry/employees)
const HISTORY_SUBSET   = 150;    // stocks to get ~1yr daily history (charts + 52wk)
const DIV_SUBSET       = 400;    // dividends endpoint calls
const SPLIT_SUBSET     = 250;    // splits endpoint calls
const ETF_CAP          = 500;    // real ETFs (price only)
const MS_MAX_CALLS     = 8000;   // hard safety stop

// ---------------------------------------------------------------------
// Marketstack HTTP client
// ---------------------------------------------------------------------
const MS_BASE = 'https://api.marketstack.com/v2';
$GLOBALS['ms_key']   = getenv('MARKETSTACK_API_KEY') ?: 'af295efd6b9797a248faa843e85d7b79';
$GLOBALS['ms_calls'] = 0;

function ms_get(string $path, array $query = []): ?array
{
    if ($GLOBALS['ms_calls'] >= MS_MAX_CALLS) {
        throw new RuntimeException('Marketstack request budget exhausted (' . MS_MAX_CALLS . ').');
    }
    $query['access_key'] = $GLOBALS['ms_key'];
    $url = MS_BASE . '/' . ltrim($path, '/') . '?' . http_build_query($query);
    for ($try = 0; $try < 4; $try++) {
        $ctx = stream_context_create(['http' => [
            'method' => 'GET', 'timeout' => 45, 'ignore_errors' => true,
            'header' => "Accept: application/json\r\n",
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        $GLOBALS['ms_calls']++;
        $code = 0;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('#HTTP/\S+\s+(\d+)#', $h, $m)) { $code = (int) $m[1]; }
            }
        }
        if ($raw !== false && $raw !== '' && $code >= 200 && $code < 300) {
            $d = json_decode($raw, true);
            return is_array($d) ? $d : null;
        }
        if ($code === 429 || $code >= 500 || $raw === false) {
            usleep(1500000 * ($try + 1));
            continue;
        }
        return null; // 4xx (other than 429) - unrecoverable for this request
    }
    return null;
}

/** Page through a list endpoint up to $max items. */
function ms_paged(string $path, array $query, int $max, int $pageSize = 1000): array
{
    $out = [];
    $offset = 0;
    do {
        $q = $query; $q['limit'] = $pageSize; $q['offset'] = $offset;
        $r = ms_get($path, $q);
        if (!$r || empty($r['data'])) { break; }
        foreach ($r['data'] as $d) {
            $out[] = $d;
            if (count($out) >= $max) { return $out; }
        }
        $total = (int) ($r['pagination']['total'] ?? 0);
        $cnt = (int) ($r['pagination']['count'] ?? count($r['data']));
        $offset += $pageSize;
        if ($cnt < $pageSize || ($total > 0 && $offset >= $total)) { break; }
    } while (count($out) < $max);
    return $out;
}

// ---------------------------------------------------------------------
// Shared seeder helpers (mirror database/seed.php)
// ---------------------------------------------------------------------
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

$log('=== xbt.co.il Marketstack importer starting ===');
$log('API key: ' . substr($GLOBALS['ms_key'], 0, 6) . '...');

// ---------------------------------------------------------------------
// Truncate (clean slate)
// ---------------------------------------------------------------------
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$tables = ['faqs','stock_themes','etf_themes','stock_prices','stock_financials','stock_dividends','stock_splits',
    'etf_holdings','index_constituents','options_contracts','futures_contracts',
    'stocks','etfs','bonds','indices','reits','cryptos','commodities','currencies',
    'industries','sectors','themes','exchanges','countries',
    'guides','guide_categories','glossary_terms','comparisons','news','news_categories',
    'api_sources','site_settings'];
foreach ($tables as $t) { $pdo->exec("TRUNCATE TABLE `$t`"); }
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
$log('Cleared existing data.');

// ---------------------------------------------------------------------
// Country reference (ISO2 -> name/he/flag). Derived; not from API.
// ---------------------------------------------------------------------
$COUNTRY_MAP = [
    'US'=>['United States','ארצות הברית','USD','🇺🇸','North America'],
    'HK'=>['Hong Kong','הונג קונג','HKD','🇭🇰','Asia'],
    'CN'=>['China','סין','CNY','🇨🇳','Asia'],
    'GB'=>['United Kingdom','בריטניה','GBP','🇬🇧','Europe'],
    'JP'=>['Japan','יפן','JPY','🇯🇵','Asia'],
    'DE'=>['Germany','גרמניה','EUR','🇩🇪','Europe'],
    'FR'=>['France','צרפת','EUR','🇫🇷','Europe'],
    'CA'=>['Canada','קנדה','CAD','🇨🇦','North America'],
    'AU'=>['Australia','אוסטרליה','AUD','🇦🇺','Oceania'],
    'CH'=>['Switzerland','שווייץ','CHF','🇨🇭','Europe'],
    'NL'=>['Netherlands','הולנד','EUR','🇳🇱','Europe'],
    'IT'=>['Italy','איטליה','EUR','🇮🇹','Europe'],
    'ES'=>['Spain','ספרד','EUR','🇪🇸','Europe'],
    'KR'=>['South Korea','דרום קוריאה','KRW','🇰🇷','Asia'],
    'IN'=>['India','הודו','INR','🇮🇳','Asia'],
    'BR'=>['Brazil','ברזיל','BRL','🇧🇷','South America'],
    'TW'=>['Taiwan','טאיוואן','TWD','🇹🇼','Asia'],
    'SG'=>['Singapore','סינגפור','SGD','🇸🇬','Asia'],
    'IL'=>['Israel','ישראל','ILS','🇮🇱','Middle East'],
    'SE'=>['Sweden','שוודיה','SEK','🇸🇪','Europe'],
    'NO'=>['Norway','נורווגיה','NOK','🇳🇴','Europe'],
    'DK'=>['Denmark','דנמרק','DKK','🇩🇰','Europe'],
    'FI'=>['Finland','פינלנד','EUR','🇫🇮','Europe'],
    'IE'=>['Ireland','אירלנד','EUR','🇮🇪','Europe'],
    'BE'=>['Belgium','בלגיה','EUR','🇧🇪','Europe'],
    'AT'=>['Austria','אוסטריה','EUR','🇦🇹','Europe'],
    'PT'=>['Portugal','פורטוגל','EUR','🇵🇹','Europe'],
    'MX'=>['Mexico','מקסיקו','MXN','🇲🇽','North America'],
    'ZA'=>['South Africa','דרום אפריקה','ZAR','🇿🇦','Africa'],
    'NZ'=>['New Zealand','ניו זילנד','NZD','🇳🇿','Oceania'],
];
$GLOBALS['xbt_country_id'] = []; // iso2 => id

function ensure_country(PDO $pdo, string $iso2): ?int
{
    $iso2 = strtoupper(trim($iso2));
    if ($iso2 === '' || strlen($iso2) !== 2) { return null; }
    if (isset($GLOBALS['xbt_country_id'][$iso2])) { return $GLOBALS['xbt_country_id'][$iso2]; }
    $map = $GLOBALS['COUNTRY_MAP'][$iso2] ?? [$iso2, $iso2, null, null, null];
    static $usedSlug = [];
    $slug = uslug($map[0], $usedSlug);
    $st = $pdo->prepare('INSERT INTO countries (name,name_he,slug,iso2,region,currency_code,flag_emoji,description,meta_title,meta_desc,status) VALUES (?,?,?,?,?,?,?,?,?,?,1)');
    $st->execute([
        $map[0], $map[1], $slug, $iso2, $map[4] ?? null, $map[2] ?? null, $map[3] ?? null,
        "מידע על שוק ההון ב{$map[1]}, כולל מניות, בורסות ומטבע מקומי.",
        "{$map[1]} - שוק ההון | xbt.co.il", "מניות, בורסות ונתונים פיננסיים על {$map[1]}.",
    ]);
    $id = (int) $pdo->lastInsertId();
    $GLOBALS['xbt_country_id'][$iso2] = $id;
    return $id;
}
$GLOBALS['COUNTRY_MAP'] = $COUNTRY_MAP;

// Pre-create the core countries we know we will use.
foreach (array_keys($COUNTRY_MAP) as $iso) { ensure_country($pdo, $iso); }
$log('Countries: ' . count($GLOBALS['xbt_country_id']));

// ---------------------------------------------------------------------
// Exchanges (real)
// ---------------------------------------------------------------------
$exData = ms_paged('exchanges', [], 4000);
$exRows = [];
$usedSlug = [];
$GLOBALS['xbt_exchange_id'] = []; // mic => id
foreach ($exData as $e) {
    $mic = trim((string) ($e['mic'] ?? ''));
    $name = trim((string) ($e['name'] ?? ''));
    if ($mic === '' || $name === '') { continue; }
    if (isset($GLOBALS['xbt_exchange_id'][$mic])) { continue; }
    $cid = ensure_country($pdo, (string) ($e['country_code'] ?? ''));
    $slug = uslug($name . '-' . $mic, $usedSlug);
    $exRows[] = [
        'name' => $name, 'name_he' => $name, 'slug' => $slug,
        'code' => $e['acronym'] ?? null, 'mic' => $mic, 'country_id' => $cid,
        'currency_code' => null,
        'description' => "{$name} ({$mic}) - בורסה למסחר בניירות ערך.",
        'meta_title' => "{$name} ({$mic}) | xbt.co.il",
        'meta_desc' => "מידע על בורסת {$name}: מניות, מטבע ונתוני מסחר.",
        'status' => 1,
    ];
    $GLOBALS['xbt_exchange_id'][$mic] = true; // placeholder, fill real id after insert
}
$pdo->beginTransaction();
batchInsert($pdo, 'exchanges', ['name','name_he','slug','code','mic','country_id','currency_code','description','meta_title','meta_desc','status'], $exRows, 400);
$pdo->commit();
foreach ($db->all('SELECT id, mic FROM exchanges WHERE mic IS NOT NULL') as $r) { $GLOBALS['xbt_exchange_id'][$r['mic']] = (int) $r['id']; }
$log('Exchanges: ' . count($exRows));

// ---------------------------------------------------------------------
// Currencies (real)
// ---------------------------------------------------------------------
$curData = ms_paged('currencies', [], 200, 100);
$curRows = [];
$usedSlug = [];
foreach ($curData as $c) {
    $code = trim((string) ($c['code'] ?? ''));
    $name = trim((string) ($c['name'] ?? ''));
    if ($code === '' || $name === '') { continue; }
    $curRows[] = [
        'name' => $name, 'name_he' => $name, 'slug' => uslug($name . '-' . $code, $usedSlug),
        'code' => $code, 'pair' => $code . '/USD', 'symbol' => $c['symbol'] ?? null,
        'rate' => null, 'day_change_pct' => null,
        'description' => "{$name} ({$code}) - מטבע. מידע על שער החליפין מול הדולר.",
        'description_he' => "{$name} ({$code}) - מטבע סחיר בשוק המט\"ח.",
        'meta_title' => "{$name} ({$code}) - שער מטבע | xbt.co.il",
        'meta_desc' => "מידע על המטבע {$name} ({$code}).",
        'status' => 1,
    ];
}
batchInsert($pdo, 'currencies', ['name','name_he','slug','code','pair','symbol','rate','day_change_pct','description','description_he','meta_title','meta_desc','status'], $curRows, 200);
$log('Currencies: ' . count($curRows));

// ---------------------------------------------------------------------
// Stocks, then assets, then authored content
// ---------------------------------------------------------------------
require __DIR__ . '/seeders/ms_stocks.php';
require __DIR__ . '/seeders/ms_assets.php';
require __DIR__ . '/seeders/content.php';

// ---------------------------------------------------------------------
// Summary
// ---------------------------------------------------------------------
$elapsed = round(microtime(true) - $start, 1);
$log("=== Import complete in {$elapsed}s | Marketstack calls: {$GLOBALS['ms_calls']} ===");
foreach (['countries','exchanges','currencies','sectors','industries','themes','stocks','stock_prices','stock_dividends','stock_splits','etfs','indices','bonds','guides','glossary_terms','news','comparisons','faqs'] as $t) {
    $log(sprintf('  %-18s %s', $t, number_format((int) $db->value("SELECT COUNT(*) FROM `$t`"))));
}
