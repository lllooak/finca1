<?php
/**
 * Marketstack importer 2/3: stocks (+ sectors/industries, prices, dividends,
 * splits, themes). All data is REAL from Marketstack; unknown fields stay NULL.
 *
 * @var PDO $pdo  @var \App\Core\Database $db  @var callable $log
 */

$SECTOR_HE = [
    'Technology' => 'טכנולוגיה', 'Financial Services' => 'שירותים פיננסיים', 'Healthcare' => 'בריאות',
    'Consumer Cyclical' => 'מוצרי צריכה מחזוריים', 'Consumer Defensive' => 'מוצרי צריכה הגנתיים',
    'Industrials' => 'תעשייה', 'Energy' => 'אנרגיה', 'Basic Materials' => 'חומרי גלם',
    'Real Estate' => 'נדל"ן', 'Utilities' => 'תשתיות', 'Communication Services' => 'שירותי תקשורת',
    'Financial' => 'פיננסים', 'Industrial Goods' => 'מוצרי תעשייה', 'Services' => 'שירותים',
    'Consumer Goods' => 'מוצרי צריכה',
];
$GLOBALS['SECTOR_HE'] = $SECTOR_HE;

// ---------------------------------------------------------------------
// 1) Discover real tickers (ordered by relevance, mega-caps first)
// ---------------------------------------------------------------------
$discovered = []; // ['ticker','name','mic','iso']
$seenTicker = [];
foreach (STOCK_EXCHANGES as $mic => $iso) {
    if (count($discovered) >= STOCK_CAP) { break; }
    $remaining = STOCK_CAP - count($discovered);
    $list = ms_paged('tickerslist', ['exchange' => $mic], min($remaining + 200, STOCK_CAP));
    $added = 0;
    foreach ($list as $t) {
        if (count($discovered) >= STOCK_CAP) { break; }
        if (empty($t['has_eod'])) { continue; }
        $ticker = trim((string) ($t['ticker'] ?? ''));
        $name = trim((string) ($t['name'] ?? ''));
        if ($ticker === '' || $name === '' || strlen($ticker) > 20) { continue; }
        if (isset($seenTicker[$ticker])) { continue; }
        $seenTicker[$ticker] = true;
        $discovered[] = ['ticker' => $ticker, 'name' => $name, 'mic' => $mic, 'iso' => $iso];
        $added++;
    }
    $log("  tickerslist {$mic}: +{$added} (total " . count($discovered) . ")");
}
$log('Discovered ' . count($discovered) . ' real tickers.');

// ---------------------------------------------------------------------
// 2) Fetch latest EOD + recent history (day-change + mini charts) in
//    batches of 100 symbols. One request returns ~10 days for 100 symbols.
// ---------------------------------------------------------------------
$barsByTicker = []; // ticker => [ ['date'=>,'open'=>,'high'=>,'low'=>,'close'=>,'volume'=>,'adj_close'=>,'dividend'=>,'split'=>], ... ] DESC
$metaByTicker = []; // ticker => ['name'=>,'currency'=>,'asset_type'=>,'mic'=>]
$tickers = array_column($discovered, 'ticker');
$batches = array_chunk($tickers, PRICE_BATCH);
foreach ($batches as $bi => $batch) {
    $r = ms_get('eod', ['symbols' => implode(',', $batch), 'sort' => 'DESC', 'limit' => 1000]);
    if (!$r || empty($r['data'])) { continue; }
    foreach ($r['data'] as $bar) {
        $sym = $bar['symbol'] ?? null;
        if (!$sym) { continue; }
        $barsByTicker[$sym][] = [
            'date' => substr((string) $bar['date'], 0, 10),
            'open' => $bar['open'], 'high' => $bar['high'], 'low' => $bar['low'],
            'close' => $bar['close'], 'volume' => $bar['volume'],
            'adj_close' => $bar['adj_close'] ?? $bar['close'],
            'dividend' => (float) ($bar['dividend'] ?? 0),
            'split' => (float) ($bar['split_factor'] ?? 1),
        ];
        if (!isset($metaByTicker[$sym])) {
            $metaByTicker[$sym] = [
                'name' => $bar['name'] ?? null,
                'currency' => $bar['price_currency'] ?? 'USD',
                'asset_type' => $bar['asset_type'] ?? 'Stock',
                'mic' => $bar['exchange'] ?? null,
            ];
        }
    }
    if (($bi % 5) === 0) { $log('  EOD batch ' . ($bi + 1) . '/' . count($batches) . ' (calls ' . $GLOBALS['ms_calls'] . ')'); }
}
$log('Fetched EOD for ' . count($barsByTicker) . ' tickers.');

// ---------------------------------------------------------------------
// 3) Company info (sector/industry/employees) for the top INFO_SUBSET.
//    Build sectors & industries from the real values returned.
// ---------------------------------------------------------------------
$sectorId = [];   // name => id
$industryId = []; // name => id (within sector)
$infoByTicker = []; // ticker => ['sector'=>,'industry'=>,'employees'=>,'ceo'=>,'ipo'=>]
$usedSecSlug = []; $usedIndSlug = [];

function ms_ensure_sector(PDO $pdo, string $name, array &$sectorId, array &$used): ?int {
    $name = trim($name);
    if ($name === '') { return null; }
    if (isset($sectorId[$name])) { return $sectorId[$name]; }
    $he = $GLOBALS['SECTOR_HE'][$name] ?? $name;
    $st = $pdo->prepare('INSERT INTO sectors (name,name_he,slug,description,overview,meta_title,meta_desc,status) VALUES (?,?,?,?,?,?,?,1)');
    $slug = uslug($name, $used);
    $st->execute([$name, $he, $slug, "סקטור {$he} - חברות ומניות מובילות.", "סקירת סקטור {$he} בשוק ההון.", "סקטור {$he} | xbt.co.il", "מניות וחברות בסקטור {$he}."]);
    return $sectorId[$name] = (int) $pdo->lastInsertId();
}
function ms_ensure_industry(PDO $pdo, string $name, ?int $secId, array &$industryId, array &$used): ?int {
    $name = trim($name);
    if ($name === '') { return null; }
    if (isset($industryId[$name])) { return $industryId[$name]; }
    $st = $pdo->prepare('INSERT INTO industries (sector_id,name,name_he,slug,description,overview,meta_title,meta_desc,status) VALUES (?,?,?,?,?,?,?,?,1)');
    $slug = uslug($name, $used);
    $st->execute([$secId, $name, $name, $slug, "תעשיית {$name} - חברות ומניות.", "סקירת תעשיית {$name}.", "{$name} | xbt.co.il", "מניות בתעשיית {$name}."]);
    return $industryId[$name] = (int) $pdo->lastInsertId();
}

$infoCount = min(INFO_SUBSET, count($discovered));
for ($i = 0; $i < $infoCount; $i++) {
    $tk = $discovered[$i]['ticker'];
    $info = ms_get('tickerinfo', ['ticker' => $tk]);
    $d = $info['data'] ?? null;
    if (!$d) { continue; }
    $sector = trim((string) ($d['sector'] ?? ''));
    $industry = trim((string) ($d['industry'] ?? ''));
    $infoByTicker[$tk] = [
        'sector' => $sector, 'industry' => $industry,
        'employees' => is_numeric($d['full_time_employees'] ?? null) ? (int) $d['full_time_employees'] : null,
        'ceo' => null, 'ipo' => $d['ipo_date'] ?? null,
        'founded' => is_numeric(substr((string) ($d['date_founded'] ?? ''), 0, 4)) ? (int) substr((string) $d['date_founded'], 0, 4) : null,
    ];
    if (!empty($d['key_executives'][0]['name'])) {
        foreach ($d['key_executives'] as $ex) {
            if (stripos((string) ($ex['function'] ?? ''), 'CEO') !== false) { $infoByTicker[$tk]['ceo'] = trim((string) $ex['name']); break; }
        }
    }
    if ($sector !== '') {
        $sid = ms_ensure_sector($pdo, $sector, $sectorId, $usedSecSlug);
        if ($industry !== '') { ms_ensure_industry($pdo, $industry, $sid, $industryId, $usedIndSlug); }
    }
    if (($i % 50) === 0) { $log('  tickerinfo ' . ($i + 1) . "/{$infoCount} (calls {$GLOBALS['ms_calls']})"); }
}
$log('Sectors: ' . count($sectorId) . ' | Industries: ' . count($industryId) . ' | Info: ' . count($infoByTicker));

// ---------------------------------------------------------------------
// 4) 1-year daily history for the top HISTORY_SUBSET (charts + 52wk).
// ---------------------------------------------------------------------
$histCount = min(HISTORY_SUBSET, count($discovered));
$dateFrom = date('Y-m-d', strtotime('-400 days'));
$dateTo = date('Y-m-d');
for ($i = 0; $i < $histCount; $i++) {
    $tk = $discovered[$i]['ticker'];
    $r = ms_get('eod', ['symbols' => $tk, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'sort' => 'DESC', 'limit' => 1000]);
    if (!$r || empty($r['data'])) { continue; }
    $byDate = [];
    foreach ($barsByTicker[$tk] ?? [] as $b) { $byDate[$b['date']] = $b; }
    foreach ($r['data'] as $bar) {
        $dt = substr((string) $bar['date'], 0, 10);
        $byDate[$dt] = [
            'date' => $dt, 'open' => $bar['open'], 'high' => $bar['high'], 'low' => $bar['low'],
            'close' => $bar['close'], 'volume' => $bar['volume'], 'adj_close' => $bar['adj_close'] ?? $bar['close'],
            'dividend' => (float) ($bar['dividend'] ?? 0), 'split' => (float) ($bar['split_factor'] ?? 1),
        ];
    }
    krsort($byDate);
    $barsByTicker[$tk] = array_values($byDate);
    if (($i % 30) === 0) { $log('  history ' . ($i + 1) . "/{$histCount} (calls {$GLOBALS['ms_calls']})"); }
}
$log('History enriched for top ' . $histCount . ' stocks.');

// ---------------------------------------------------------------------
// 5) Dividends & splits (top subsets)
// ---------------------------------------------------------------------
$divByTicker = []; // ticker => [ ['ex'=>,'pay'=>,'amount'=>,'freq'=>] ]
$divCount = min(DIV_SUBSET, count($discovered));
for ($i = 0; $i < $divCount; $i++) {
    $tk = $discovered[$i]['ticker'];
    $r = ms_get('dividends', ['symbols' => $tk, 'limit' => 100]);
    if (!$r || empty($r['data'])) { continue; }
    foreach ($r['data'] as $d) {
        if (empty($d['dividend'])) { continue; }
        $freqMap = ['q' => 'quarterly', 'a' => 'annual', 's' => 'semi-annual', 'm' => 'monthly'];
        $divByTicker[$tk][] = [
            'ex' => substr((string) ($d['date'] ?? ''), 0, 10),
            'pay' => $d['payment_date'] ? substr((string) $d['payment_date'], 0, 10) : null,
            'amount' => (float) $d['dividend'],
            'freq' => $freqMap[strtolower((string) ($d['distr_freq'] ?? 'q'))] ?? 'quarterly',
        ];
    }
    if (($i % 50) === 0) { $log('  dividends ' . ($i + 1) . "/{$divCount} (calls {$GLOBALS['ms_calls']})"); }
}
$splitByTicker = [];
$splitCount = min(SPLIT_SUBSET, count($discovered));
for ($i = 0; $i < $splitCount; $i++) {
    $tk = $discovered[$i]['ticker'];
    $r = ms_get('splits', ['symbols' => $tk, 'limit' => 100]);
    if (!$r || empty($r['data'])) { continue; }
    foreach ($r['data'] as $d) {
        if (empty($d['split_factor'])) { continue; }
        $splitByTicker[$tk][] = ['date' => substr((string) ($d['date'] ?? ''), 0, 10), 'factor' => (float) $d['split_factor']];
    }
    if (($i % 50) === 0) { $log('  splits ' . ($i + 1) . "/{$splitCount} (calls {$GLOBALS['ms_calls']})"); }
}
$log('Dividends for ' . count($divByTicker) . ' | Splits for ' . count($splitByTicker) . ' stocks.');

// ---------------------------------------------------------------------
// 6) Build & insert stock rows
// ---------------------------------------------------------------------
$stockRows = [];
$usedSlug = [];
foreach ($discovered as $idx => $d) {
    $tk = $d['ticker'];
    $bars = $barsByTicker[$tk] ?? [];
    if (empty($bars)) { continue; } // no real price -> skip (real data only)
    $meta = $metaByTicker[$tk] ?? [];
    $info = $infoByTicker[$tk] ?? [];

    $latest = $bars[0];
    $price = (float) $latest['close'];
    $prev = isset($bars[1]) ? (float) $bars[1]['close'] : null;
    $change = $prev !== null ? round($price - $prev, 4) : null;
    $changePct = ($prev !== null && $prev > 0) ? round(($price - $prev) / $prev * 100, 4) : null;

    // 52-week + 1y return from available history (real where we have a year)
    $closes = array_map(fn($b) => (float) $b['close'], $bars);
    $w52high = $closes ? round(max($closes), 4) : null;
    $w52low = $closes ? round(min($closes), 4) : null;
    $ret1y = null;
    $oldest = end($bars);
    if (count($bars) > 200 && !empty($oldest['close']) && (float) $oldest['close'] > 0) {
        $ret1y = round(($price - (float) $oldest['close']) / (float) $oldest['close'] * 100, 4);
    }

    $sectorIdVal = !empty($info['sector']) ? ($sectorId[$info['sector']] ?? null) : null;
    $industryIdVal = !empty($info['industry']) ? ($industryId[$info['industry']] ?? null) : null;
    $secHe = !empty($info['sector']) ? ($SECTOR_HE[$info['sector']] ?? $info['sector']) : null;

    $name = $d['name'] ?: ($meta['name'] ?? null); // prefer clean tickerslist name over EOD name field
    $mic = ($meta['mic'] ?? null) ?: $d['mic'];
    $exId = $GLOBALS['xbt_exchange_id'][$mic] ?? ($GLOBALS['xbt_exchange_id'][$d['mic']] ?? null);
    $cid = ensure_country($pdo, $d['iso']);
    $currency = ($meta['currency'] ?? null) ?: ($d['iso'] === 'HK' ? 'HKD' : 'USD');

    $exName = '';
    if ($exId) { $exName = (string) $db->value('SELECT name FROM exchanges WHERE id = ?', [$exId]); }
    $descHe = "{$name} ({$tk}) היא חברה ציבורית הנסחרת בבורסת {$exName}." .
        ($secHe ? " החברה פועלת בסקטור {$secHe}" . (!empty($info['industry']) ? " בתעשיית {$info['industry']}" : '') . '.' : '') .
        " בעמוד זה תמצאו נתוני מסחר עדכניים, היסטוריית מחירים, דיבידנדים ומידע נוסף על המניה.";

    $stockRows[] = [
        'ticker' => $tk, 'company_name' => $name, 'company_name_he' => $name,
        'slug' => uslug($tk . '-' . $name, $usedSlug),
        'exchange_id' => $exId, 'country_id' => $cid, 'sector_id' => $sectorIdVal, 'industry_id' => $industryIdVal,
        'currency_code' => substr($currency, 0, 3),
        'website' => null, 'ceo' => $info['ceo'] ?? null, 'founded_year' => $info['founded'] ?? null,
        'employees' => $info['employees'] ?? null,
        'price' => round($price, 4), 'prev_close' => $prev !== null ? round($prev, 4) : null,
        'day_change' => $change, 'day_change_pct' => $changePct,
        'day_high' => $latest['high'] !== null ? round((float) $latest['high'], 4) : null,
        'day_low' => $latest['low'] !== null ? round((float) $latest['low'], 4) : null,
        'volume' => $latest['volume'] !== null ? (int) $latest['volume'] : null,
        'week52_high' => $w52high, 'week52_low' => $w52low, 'return_1y' => $ret1y,
        'description_he' => $descHe,
        'is_active' => 1, 'is_featured' => $idx < 60 ? 1 : 0,
        'meta_title' => "{$name} ({$tk}) - מחיר מניה ונתונים | xbt.co.il",
        'meta_desc' => "מחיר מניית {$name} ({$tk}), שינוי יומי, היסטוריית מחירים ודיבידנדים.",
        'status' => 1,
    ];
}
$cols = ['ticker','company_name','company_name_he','slug','exchange_id','country_id','sector_id','industry_id','currency_code','website','ceo','founded_year','employees','price','prev_close','day_change','day_change_pct','day_high','day_low','volume','week52_high','week52_low','return_1y','description_he','is_active','is_featured','meta_title','meta_desc','status'];
$pdo->beginTransaction();
batchInsert($pdo, 'stocks', $cols, $stockRows, 300);
$pdo->commit();
$idByTicker = [];
foreach ($db->all('SELECT id, ticker FROM stocks') as $r) { $idByTicker[$r['ticker']] = (int) $r['id']; }
$log('Stocks inserted: ' . count($stockRows));

// ---------------------------------------------------------------------
// 7) Price history, dividends, splits (real)
// ---------------------------------------------------------------------
$priceRows = [];
foreach ($barsByTicker as $tk => $bars) {
    $sid = $idByTicker[$tk] ?? null; if (!$sid) { continue; }
    foreach ($bars as $b) {
        if ($b['close'] === null || $b['date'] === '') { continue; }
        $priceRows[] = ['stock_id' => $sid, 'price_date' => $b['date'],
            'open' => $b['open'], 'high' => $b['high'], 'low' => $b['low'], 'close' => $b['close'],
            'adj_close' => $b['adj_close'], 'volume' => $b['volume'] !== null ? (int) $b['volume'] : null];
    }
    if (count($priceRows) >= 20000) { $pdo->beginTransaction(); batchInsert($pdo, 'stock_prices', ['stock_id','price_date','open','high','low','close','adj_close','volume'], $priceRows, 500); $pdo->commit(); $priceRows = []; }
}
if ($priceRows) { $pdo->beginTransaction(); batchInsert($pdo, 'stock_prices', ['stock_id','price_date','open','high','low','close','adj_close','volume'], $priceRows, 500); $pdo->commit(); }
$log('Price history rows inserted.');

$divRows = [];
foreach ($divByTicker as $tk => $divs) {
    $sid = $idByTicker[$tk] ?? null; if (!$sid) { continue; }
    foreach ($divs as $d) {
        if ($d['ex'] === '') { continue; }
        $divRows[] = ['stock_id' => $sid, 'ex_date' => $d['ex'], 'pay_date' => $d['pay'], 'amount' => $d['amount'], 'frequency' => $d['freq']];
    }
}
if ($divRows) { $pdo->beginTransaction(); batchInsert($pdo, 'stock_dividends', ['stock_id','ex_date','pay_date','amount','frequency'], $divRows, 500); $pdo->commit(); }
$log('Dividend rows: ' . count($divRows));

// Update dividend yield + trailing dividend on stocks from latest 4 payments
foreach ($divByTicker as $tk => $divs) {
    $sid = $idByTicker[$tk] ?? null; if (!$sid || empty($divs)) { continue; }
    usort($divs, fn($a, $b) => strcmp($b['ex'], $a['ex']));
    $ttm = 0.0; $n = 0;
    foreach ($divs as $d) { if ($n++ >= 4) break; $ttm += $d['amount']; }
    $price = (float) $db->value('SELECT price FROM stocks WHERE id = ?', [$sid]);
    $yield = $price > 0 ? round($ttm / $price * 100, 4) : null;
    $pdo->prepare('UPDATE stocks SET dividend = ?, dividend_yield = ?, ex_dividend_date = ? WHERE id = ?')
        ->execute([round($ttm, 4), $yield, $divs[0]['ex'] ?: null, $sid]);
}

$splitRows = [];
foreach ($splitByTicker as $tk => $splits) {
    $sid = $idByTicker[$tk] ?? null; if (!$sid) { continue; }
    $seen = [];
    foreach ($splits as $s) {
        if ($s['date'] === '' || isset($seen[$s['date']])) { continue; }
        $seen[$s['date']] = true;
        $splitRows[] = ['stock_id' => $sid, 'split_date' => $s['date'], 'split_factor' => $s['factor']];
    }
}
if ($splitRows) { $pdo->beginTransaction(); batchInsert($pdo, 'stock_splits', ['stock_id','split_date','split_factor'], $splitRows, 500); $pdo->commit(); }
$log('Split rows: ' . count($splitRows));

// ---------------------------------------------------------------------
// 8) Themes (curated) + keyword/sector/country based linking to real stocks
// ---------------------------------------------------------------------
$THEMES = [
    ['Artificial Intelligence','בינה מלאכותית','Technology',['ai','artificial','software','semiconductor','cloud','data']],
    ['Semiconductors','מוליכים למחצה','Technology',['semiconductor','chip']],
    ['Software','תוכנה','Technology',['software','application','internet']],
    ['Biotechnology','ביוטכנולוגיה','Healthcare',['biotech','pharma','drug','therapeut','medical']],
    ['Banks','בנקים','Financial Services',['bank']],
    ['Energy','אנרגיה','Energy',['oil','gas','energy','petroleum']],
    ['Electric Vehicles','רכב חשמלי','Consumer Cyclical',['auto','vehicle','electric']],
    ['Retail','קמעונאות','Consumer Cyclical',['retail','store','commerce']],
    ['Real Estate','נדל"ן','Real Estate',['reit','real estate','property']],
    ['Hong Kong','הונג קונג','Markets',[]],
    ['China','סין','Markets',[]],
    ['Dividend Stocks','מניות דיבידנד','Income',[]],
];
$themeRows = [];
$usedSlug = [];
foreach ($THEMES as $t) {
    $themeRows[] = ['name' => $t[0], 'name_he' => $t[1], 'slug' => uslug($t[0], $usedSlug), 'category' => $t[2],
        'description' => "מניות בנושא {$t[1]}.", 'overview' => "סקירת תחום {$t[1]} ומניות מובילות בו.",
        'is_featured' => 1, 'meta_title' => "{$t[1]} - מניות | xbt.co.il", 'meta_desc' => "המניות המובילות בתחום {$t[1]}.", 'status' => 1];
}
batchInsert($pdo, 'themes', ['name','name_he','slug','category','description','overview','is_featured','meta_title','meta_desc','status'], $themeRows);
$themeIdByName = [];
foreach ($db->all('SELECT id, name FROM themes') as $r) { $themeIdByName[$r['name']] = (int) $r['id']; }

// Link stocks to themes
$allStocks = $db->all('SELECT s.id, s.ticker, s.company_name, s.industry_id, s.dividend_yield, s.country_id, i.name AS industry FROM stocks s LEFT JOIN industries i ON i.id = s.industry_id');
$hkCountry = $GLOBALS['xbt_country_id']['HK'] ?? -1;
$cnCountry = $GLOBALS['xbt_country_id']['CN'] ?? -1;
$linkRows = [];
$linkSeen = [];
foreach ($allStocks as $s) {
    $hay = strtolower(($s['company_name'] ?? '') . ' ' . ($s['industry'] ?? ''));
    foreach ($THEMES as $t) {
        $tid = $themeIdByName[$t[0]] ?? null; if (!$tid) { continue; }
        $match = false;
        if ($t[0] === 'Hong Kong') { $match = ((int) $s['country_id'] === $hkCountry); }
        elseif ($t[0] === 'China') { $match = ((int) $s['country_id'] === $cnCountry); }
        elseif ($t[0] === 'Dividend Stocks') { $match = ((float) ($s['dividend_yield'] ?? 0) >= 2.0); }
        else { foreach ($t[3] as $kw) { if ($kw !== '' && strpos($hay, $kw) !== false) { $match = true; break; } } }
        if ($match) {
            $key = $s['id'] . ':' . $tid;
            if (isset($linkSeen[$key])) { continue; }
            $linkSeen[$key] = true;
            $linkRows[] = ['stock_id' => (int) $s['id'], 'theme_id' => $tid];
        }
    }
}
if ($linkRows) { $pdo->beginTransaction(); batchInsert($pdo, 'stock_themes', ['stock_id','theme_id'], $linkRows, 500); $pdo->commit(); }
$log('Themes: ' . count($themeRows) . ' | stock-theme links: ' . count($linkRows));

$GLOBALS['xbt_stocks'] = ['idByTicker' => $idByTicker];
