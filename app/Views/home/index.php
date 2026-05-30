<?php
use App\Core\View;
/** @var array $stats,$indices,$trending,$gainers,$losers,$active,$dividend,$topus,$tophk,$etfs,$reits,$bonds,$crypto,$commodities,$sectors,$themes,$guides_l,$comparisons,$news,$faqs,$themeStocks */

// helper to render a stock panel
$panel = function (string $title, string $icon, array $rows, string $moreUrl = '', bool $div = false) {
    echo '<div class="col-lg-6"><div class="panel section"><div class="panel-head"><h2><i class="bi ' . $icon . '"></i> ' . e($title) . '</h2>';
    if ($moreUrl) echo '<a class="more" href="' . $moreUrl . '">הצג הכל ‹</a>';
    echo '</div><div class="panel-body p-0">';
    echo View::partial('partials/stocks_table', ['rows' => $rows, 'showDividend' => $div]);
    echo '</div></div></div>';
};
?>

<section class="hero">
  <div class="container">
    <h1>המאגר הפיננסי המעמיק בעברית על שוק ההון</h1>
    <p class="lead">מניות, ETF, אג"ח, מדדים, קריפטו, סקטורים, Themes, מדריכים והשוואות — נתונים, ניתוחים ומידע מקצועי על השווקים בארה"ב ובבורסת הונג קונג.</p>

    <form class="hero-search" action="<?= url('search') ?>" method="get">
      <i class="bi bi-search"></i>
      <input type="text" name="q" class="form-control" placeholder="חפש מניה, ETF, מדד, סקטור או מונח פיננסי...">
    </form>

    <div class="hero-chips">
      <a href="<?= url('screener/ai') ?>">מניות בינה מלאכותית</a>
      <a href="<?= url('screener/robotics') ?>">רובוטיקה</a>
      <a href="<?= url('screener/dividend') ?>">מניות דיבידנד</a>
      <a href="<?= url('screener/hong-kong') ?>">הונג קונג</a>
      <a href="<?= url('theme/semiconductors') ?>">מוליכים למחצה</a>
      <a href="<?= url('etfs') ?>">קרנות סל</a>
    </div>

    <div class="hero-stats">
      <div class="hero-stat"><div class="num"><?= int_num($stats['stocks']) ?></div><div class="lbl">מניות</div></div>
      <div class="hero-stat"><div class="num"><?= int_num($stats['etfs']) ?></div><div class="lbl">קרנות סל</div></div>
      <div class="hero-stat"><div class="num"><?= int_num($stats['indices']) ?></div><div class="lbl">מדדים</div></div>
      <div class="hero-stat"><div class="num"><?= int_num($stats['guides']) ?></div><div class="lbl">מדריכים</div></div>
      <div class="hero-stat"><div class="num"><?= int_num($stats['glossary']) ?></div><div class="lbl">מונחים</div></div>
    </div>
  </div>
</section>

<div class="container">
  <!-- Market overview -->
  <div class="market-strip">
    <?php foreach ($indices as $ix): ?>
    <a class="mkt-card" href="<?= url('index/' . e($ix['slug'])) ?>">
      <div class="mc-name"><?= e($ix['name_he'] ?: $ix['name']) ?></div>
      <div class="mc-val"><?= num($ix['value']) ?></div>
      <div class="mc-chg <?= change_class($ix['day_change_pct']) ?>"><?= pct($ix['day_change_pct']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="row">
    <?php
      $panel('המניות המובילות בארה"ב', 'bi-flag-fill', $topus, url('screener/value'));
      $panel('הנסחרות ביותר', 'bi-activity', $active, url('screener'));
      $panel('העולות ביותר', 'bi-graph-up-arrow', $gainers, url('markets'));
      $panel('היורדות ביותר', 'bi-graph-down-arrow', $losers, url('markets'));
      $panel('מניות דיבידנד מובילות', 'bi-cash-coin', $dividend, url('screener/dividend'), true);
      $panel('מניות הונג קונג מובילות', 'bi-buildings', $tophk, url('screener/hong-kong'));
    ?>
  </div>

  <!-- Theme sections -->
  <div class="row">
    <?php
      $panel('מניות בינה מלאכותית (AI)', 'bi-cpu', $themeStocks['ai'], url('theme/artificial-intelligence'));
      $panel('מניות רובוטיקה', 'bi-robot', $themeStocks['robot'], url('theme/robotics'));
      $panel('מניות מוליכים למחצה', 'bi-memory', $themeStocks['semi'], url('theme/semiconductors'));
      $panel('מחשוב קוונטי', 'bi-diagram-3', $themeStocks['quantum'], url('theme/quantum-computing'));
    ?>
  </div>

  <!-- ETFs / REITs / Bonds / Crypto -->
  <div class="row">
    <div class="col-lg-6">
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-collection"></i> קרנות סל מובילות (ETF)</h2><a class="more" href="<?= url('etfs') ?>">הצג הכל ‹</a></div>
        <div class="panel-body p-0">
          <div class="table-responsive"><table class="table table-hover asset-table mb-0">
            <thead><tr><th>סימול</th><th>שם</th><th class="text-start">מחיר</th><th class="text-start">שינוי</th><th class="text-start d-none d-md-table-cell">נכסים</th><th class="text-start d-none d-lg-table-cell">דמי ניהול</th></tr></thead>
            <tbody>
            <?php foreach ($etfs as $e): ?>
              <tr>
                <td><a class="ticker-link" href="<?= url('etf/' . e($e['ticker'])) ?>"><?= e($e['ticker']) ?></a></td>
                <td class="asset-name"><a href="<?= url('etf/' . e($e['ticker'])) ?>"><?= e($e['name_he'] ?: $e['name']) ?></a></td>
                <td class="text-start"><?= money($e['price'], $e['currency_code']) ?></td>
                <td class="text-start <?= change_class($e['day_change_pct']) ?>"><?= pct($e['day_change_pct']) ?></td>
                <td class="text-start d-none d-md-table-cell"><?= big_number($e['aum'], $e['currency_code']) ?></td>
                <td class="text-start d-none d-lg-table-cell"><?= $e['expense_ratio'] ? num($e['expense_ratio']) . '%' : '—' ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-building"></i> קרנות REIT מובילות</h2><a class="more" href="<?= url('reits') ?>">הצג הכל ‹</a></div>
        <div class="panel-body p-0">
          <div class="table-responsive"><table class="table table-hover asset-table mb-0">
            <thead><tr><th>סימול</th><th>שם</th><th class="text-start">מחיר</th><th class="text-start">שינוי</th><th class="text-start d-none d-lg-table-cell">תשואת דיב'</th></tr></thead>
            <tbody>
            <?php foreach ($reits as $r): ?>
              <tr>
                <td><a class="ticker-link" href="<?= url('reit/' . e($r['ticker'])) ?>"><?= e($r['ticker']) ?></a></td>
                <td class="asset-name"><a href="<?= url('reit/' . e($r['ticker'])) ?>"><?= e($r['name_he'] ?: $r['name']) ?></a></td>
                <td class="text-start"><?= money($r['price'], $r['currency_code']) ?></td>
                <td class="text-start <?= change_class($r['day_change_pct']) ?>"><?= pct($r['day_change_pct']) ?></td>
                <td class="text-start d-none d-lg-table-cell"><?= $r['dividend_yield'] ? num($r['dividend_yield']) . '%' : '—' ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-6">
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-currency-bitcoin"></i> קריפטו מוביל</h2><a class="more" href="<?= url('crypto') ?>">הצג הכל ‹</a></div>
        <div class="panel-body p-0"><div class="table-responsive"><table class="table table-hover asset-table mb-0">
          <thead><tr><th>סמל</th><th>שם</th><th class="text-start">מחיר</th><th class="text-start">שינוי 24ש'</th><th class="text-start d-none d-md-table-cell">שווי שוק</th></tr></thead>
          <tbody>
          <?php foreach ($crypto as $c): ?>
            <tr>
              <td><a class="ticker-link" href="<?= url('crypto/' . e($c['slug'])) ?>"><?= e($c['symbol']) ?></a></td>
              <td class="asset-name"><a href="<?= url('crypto/' . e($c['slug'])) ?>"><?= e($c['name_he'] ?: $c['name']) ?></a></td>
              <td class="text-start"><?= money($c['price'], 'USD', $c['price'] < 1 ? 4 : 2) ?></td>
              <td class="text-start <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></td>
              <td class="text-start d-none d-md-table-cell"><?= big_number($c['market_cap'], 'USD') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-bank"></i> אג"ח מובילות</h2><a class="more" href="<?= url('bonds') ?>">הצג הכל ‹</a></div>
        <div class="panel-body p-0"><div class="table-responsive"><table class="table table-hover asset-table mb-0">
          <thead><tr><th>שם</th><th class="text-start">סוג</th><th class="text-start">תשואה</th><th class="text-start d-none d-md-table-cell">דירוג</th><th class="text-start d-none d-lg-table-cell">מח"מ</th></tr></thead>
          <tbody>
          <?php foreach ($bonds as $b): ?>
            <tr>
              <td class="asset-name"><a href="<?= url('bond/' . e($b['slug'])) ?>"><?= e($b['name_he'] ?: $b['name']) ?></a></td>
              <td class="text-start"><span class="tag-pill"><?= e($b['bond_type']) ?></span></td>
              <td class="text-start text-up"><?= num($b['yield']) ?>%</td>
              <td class="text-start d-none d-md-table-cell"><span class="rating-badge"><?= e($b['credit_rating']) ?></span></td>
              <td class="text-start d-none d-lg-table-cell"><?= num($b['maturity_years'], 1) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div></div>
      </div>
    </div>
  </div>

  <!-- Commodities strip -->
  <div class="section">
    <h2 class="section-title"><i class="bi bi-droplet-half"></i> סחורות <a class="more" href="<?= url('commodities') ?>">הצג הכל ‹</a></h2>
    <div class="chip-grid">
      <?php foreach ($commodities as $c): ?>
      <a class="chip-card" href="<?= url('commodity/' . e($c['slug'])) ?>">
        <div class="cc-title"><?= e($c['name_he'] ?: $c['name']) ?></div>
        <div class="cc-meta"><?= money($c['price'], $c['currency_code']) ?> · <span class="<?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Sectors -->
  <div class="section">
    <h2 class="section-title"><i class="bi bi-grid-3x3-gap"></i> סקטורים מובילים <a class="more" href="<?= url('sectors') ?>">הצג הכל ‹</a></h2>
    <div class="chip-grid">
      <?php foreach ($sectors as $s): ?>
      <a class="chip-card" href="<?= url('sector/' . e($s['slug'])) ?>">
        <div class="cc-title"><i class="bi <?= e($s['icon'] ?: 'bi-diagram-2') ?>"></i> <?= e($s['name_he'] ?: $s['name']) ?></div>
        <div class="cc-meta">צפה במניות ובניתוח הסקטור</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Themes -->
  <div class="section">
    <h2 class="section-title"><i class="bi bi-stars"></i> Themes להשקעה <a class="more" href="<?= url('themes') ?>">הצג הכל ‹</a></h2>
    <div class="chip-grid">
      <?php foreach ($themes as $t): ?>
      <a class="chip-card" href="<?= url('theme/' . e($t['slug'])) ?>">
        <div class="cc-title"><i class="bi <?= e($t['icon'] ?: 'bi-lightning-charge') ?>"></i> <?= e($t['name_he'] ?: $t['name']) ?></div>
        <div class="cc-meta"><?= e($t['category'] ?: 'Theme השקעה') ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Guides + Comparisons -->
  <div class="row">
    <div class="col-lg-6 section">
      <h2 class="section-title"><i class="bi bi-book"></i> מדריכים נבחרים <a class="more" href="<?= url('guides') ?>">הצג הכל ‹</a></h2>
      <?php foreach ($guides_l as $g): ?>
      <a class="chip-card mb-2" href="<?= url('guide/' . e($g['slug'])) ?>">
        <div class="cc-title"><?= e($g['title']) ?></div>
        <div class="cc-meta"><?= e(excerpt($g['summary'], 110)) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="col-lg-6 section">
      <h2 class="section-title"><i class="bi bi-bar-chart-steps"></i> השוואות נבחרות <a class="more" href="<?= url('compare') ?>">הצג הכל ‹</a></h2>
      <?php foreach ($comparisons as $c): ?>
      <a class="chip-card mb-2" href="<?= url('compare/' . e($c['slug'])) ?>">
        <div class="cc-title"><?= e($c['title']) ?></div>
        <div class="cc-meta"><?= e(excerpt($c['summary'], 110)) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- News -->
  <div class="section">
    <h2 class="section-title"><i class="bi bi-newspaper"></i> חדשות אחרונות מהשווקים <a class="more" href="<?= url('news') ?>">הצג הכל ‹</a></h2>
    <div class="row gy-3">
      <?php foreach ($news as $n): ?>
      <div class="col-md-6 col-lg-3">
        <article class="news-card">
          <div class="nc-body">
            <div class="nc-cat"><?= e($n['cat_he'] ?: $n['cat'] ?: 'חדשות') ?></div>
            <h3><a href="<?= url('news/' . e($n['slug'])) ?>"><?= e($n['title']) ?></a></h3>
            <p class="text-soft small"><?= e(excerpt($n['summary'], 90)) ?></p>
            <div class="nc-meta"><?= he_date($n['published_at']) ?></div>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
