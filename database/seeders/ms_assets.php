<?php
/**
 * Marketstack importer 3/3: indices (real value + returns) and ETFs
 * (real latest price). Bonds/commodities are not available on the Basic
 * plan, so they are intentionally left empty (no fabricated data).
 *
 * @var PDO $pdo  @var \App\Core\Database $db  @var callable $log
 */

function pct_to_float($v): ?float {
    if ($v === null) { return null; }
    $v = trim(str_replace('%', '', (string) $v));
    return is_numeric($v) ? (float) $v : null;
}

// ---------------------------------------------------------------------
// Indices (real) - indexlist gives benchmark slugs, indexinfo gives data
// ---------------------------------------------------------------------
$idxList = ms_paged('indexlist', [], 300, 1000);
$idxRows = [];
$usedSlug = [];
$nameToIso = [];
foreach ($GLOBALS['COUNTRY_MAP'] as $iso => $m) { $nameToIso[strtolower($m[0])] = $iso; }

$idxCount = 0;
foreach ($idxList as $row) {
    $bench = trim((string) ($row['benchmark'] ?? ''));
    if ($bench === '') { continue; }
    $info = ms_get('indexinfo', ['index' => $bench]);
    $d = (is_array($info) && isset($info[0])) ? $info[0] : (is_array($info) && isset($info['benchmark']) ? $info : null);
    $display = ucwords(str_replace(['_', '-'], ' ', $bench));
    $cid = null;
    if ($d && !empty($d['country'])) {
        $iso = $nameToIso[strtolower((string) $d['country'])] ?? null;
        if ($iso) { $cid = ensure_country($pdo, $iso); }
    }
    $value = $d && isset($d['price']) && is_numeric($d['price']) ? (float) $d['price'] : null;
    $dayPct = $d ? pct_to_float($d['percentage_day'] ?? null) : null;
    $yearPct = $d ? pct_to_float($d['percentage_year'] ?? null) : null;
    $idxRows[] = [
        'name' => $display, 'name_he' => $display, 'slug' => uslug($bench, $usedSlug), 'symbol' => strtoupper($bench),
        'country_id' => $cid, 'value' => $value, 'day_change_pct' => $dayPct, 'return_1y' => $yearPct,
        'description' => "מדד {$display} - נתוני ערך וביצועים עדכניים.",
        'description_he' => "מדד {$display} עוקב אחר ביצועי קבוצת ניירות ערך. בעמוד זה ערך המדד והתשואות התקופתיות.",
        'is_featured' => $idxCount < 12 ? 1 : 0,
        'meta_title' => "מדד {$display} - ערך ותשואה | xbt.co.il", 'meta_desc' => "ערך מדד {$display}, שינוי יומי ותשואה שנתית.",
        'status' => 1,
    ];
    $idxCount++;
    if (($idxCount % 25) === 0) { $log('  indexinfo ' . $idxCount . '/' . count($idxList) . " (calls {$GLOBALS['ms_calls']})"); }
}
if ($idxRows) { batchInsert($pdo, 'indices', ['name','name_he','slug','symbol','country_id','value','day_change_pct','return_1y','description','description_he','is_featured','meta_title','meta_desc','status'], $idxRows, 200); }
$log('Indices: ' . count($idxRows));

// ---------------------------------------------------------------------
// ETFs (real prices for major US ETFs)
// ---------------------------------------------------------------------
$ETF_TICKERS = ['SPY','IVV','VOO','VTI','QQQ','VEA','VTV','BND','AGG','VUG','VWO','IEFA','IJR','IJH','IWF','IWM','VIG','VXUS','VO','GLD','VYM','XLK','XLF','XLE','XLV','XLY','XLI','XLP','XLU','XLB','XLRE','XLC','SCHD','DIA','IWD','EFA','EEM','VGT','SMH','SOXX','ARKK','TLT','IEF','SHY','LQD','HYG','VB','VEU','VT','ITOT','RSP','QUAL','MTUM','USMV','VNQ','SCHX','SCHB','SCHF','SCHG','JEPI','JEPQ','DVY','SDY','NOBL','VHT','VFH','VDE','VPU','VAW','VIS','VCR','VDC','VOX','KRE','XBI','IBB','GDX','SLV','USO','BIL','GOVT','MUB','TIP','VTEB','EMB','BNDX','IXUS','ACWI','EWJ','EWZ','EWG','EWH','FXI','MCHI','INDA','EWT','EWY','VGK','SPLG','SPYG','SPYV','IWB','IWR','IVW','IVE','MDY'];
$ETF_TICKERS = array_slice($ETF_TICKERS, 0, ETF_CAP);
$etfBars = [];
$etfMeta = [];
foreach (array_chunk($ETF_TICKERS, PRICE_BATCH) as $batch) {
    $r = ms_get('eod', ['symbols' => implode(',', $batch), 'sort' => 'DESC', 'limit' => 1000]);
    if (!$r || empty($r['data'])) { continue; }
    foreach ($r['data'] as $bar) {
        $sym = $bar['symbol'] ?? null; if (!$sym) { continue; }
        $etfBars[$sym][] = ['close' => $bar['close'], 'date' => substr((string) $bar['date'], 0, 10)];
        if (!isset($etfMeta[$sym])) {
            $etfMeta[$sym] = ['name' => $bar['name'] ?? $sym, 'currency' => $bar['price_currency'] ?? 'USD', 'mic' => $bar['exchange'] ?? null];
        }
    }
}
$etfRows = [];
$usedSlug = [];
$usCountry = ensure_country($pdo, 'US');
foreach ($ETF_TICKERS as $i => $tk) {
    $bars = $etfBars[$tk] ?? []; if (empty($bars)) { continue; }
    $meta = $etfMeta[$tk] ?? [];
    $price = (float) $bars[0]['close'];
    $prev = isset($bars[1]) ? (float) $bars[1]['close'] : null;
    $dayPct = ($prev !== null && $prev > 0) ? round(($price - $prev) / $prev * 100, 4) : null;
    $exId = $meta['mic'] ? ($GLOBALS['xbt_exchange_id'][$meta['mic']] ?? null) : null;
    $name = $meta['name'] ?: $tk;
    $etfRows[] = [
        'ticker' => $tk, 'name' => $name, 'name_he' => $name, 'slug' => uslug($tk . '-' . $name, $usedSlug),
        'exchange_id' => $exId, 'country_id' => $usCountry, 'currency_code' => substr($meta['currency'] ?: 'USD', 0, 3),
        'price' => round($price, 4), 'day_change_pct' => $dayPct,
        'description' => "{$name} ({$tk}) - קרן סל הנסחרת בבורסה.",
        'description_he' => "{$name} ({$tk}) היא קרן סל (ETF). בעמוד זה מחיר עדכני ונתוני מסחר.",
        'is_featured' => $i < 12 ? 1 : 0,
        'meta_title' => "{$name} ({$tk}) - קרן סל | xbt.co.il", 'meta_desc' => "מחיר ונתונים על קרן הסל {$name} ({$tk}).",
        'status' => 1,
    ];
}
if ($etfRows) { batchInsert($pdo, 'etfs', ['ticker','name','name_he','slug','exchange_id','country_id','currency_code','price','day_change_pct','description','description_he','is_featured','meta_title','meta_desc','status'], $etfRows, 200); }
$log('ETFs: ' . count($etfRows));

// Bonds / commodities: not available on the Marketstack Basic plan -> skipped (no fabricated data).
$log('Bonds & commodities: skipped (not available on Marketstack Basic plan).');
