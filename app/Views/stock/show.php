<?php
use App\Core\View;
use App\Support\Content;
/** @var array $s,$faqs,$financials,$dividends,$prices,$competitors,$themes,$etfs */
$name = $s['company_name_he'] ?: $s['company_name'];
$cur = $s['currency_code'];
$chartLabels = array_map(fn($p) => $p['price_date'], $prices);
$chartData = array_map(fn($p) => (float) $p['close'], $prices);
?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>

<section class="asset-hero">
  <div class="container">
    <div class="ah-top">
      <div class="asset-logo"><?= e(mb_substr($s['ticker'], 0, 2)) ?></div>
      <div>
        <h1><?= e($name) ?> <span class="text-soft" style="font-size:1.1rem">(<?= e($s['ticker']) ?>)</span></h1>
        <div class="ah-meta">
          <?= e($s['exchange_name_he'] ?: $s['exchange_name'] ?: '') ?>
          <?php if ($s['sector_slug']): ?> · <a href="<?= url('sector/' . e($s['sector_slug'])) ?>"><?= e($s['sector_name_he'] ?: $s['sector_name']) ?></a><?php endif; ?>
          <?php if ($s['country_slug']): ?> · <a href="<?= url('country/' . e($s['country_slug'])) ?>"><?= e($s['country_name_he'] ?: $s['country_name']) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="asset-price">
      <span class="px"><?= money($s['price'], $cur) ?></span>
      <span class="chg <?= change_class($s['day_change_pct']) ?>"><?= money($s['day_change'], $cur) ?> (<?= pct($s['day_change_pct']) ?>)</span>
    </div>
    <?php if (!empty($themes)): ?>
    <div class="asset-tags">
      <?php foreach ($themes as $t): ?><a href="<?= url('theme/' . e($t['slug'])) ?>"><?= e($t['name_he'] ?: $t['name']) ?></a><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<div class="container py-4">
  <div class="row">
    <div class="col-lg-9">
      <!-- Key stats -->
      <div class="stat-grid mb-4">
        <div class="stat"><div class="k">שווי שוק</div><div class="v"><?= big_number($s['market_cap'], $cur) ?></div></div>
        <div class="stat"><div class="k">מכפיל P/E</div><div class="v"><?= num($s['pe_ratio']) ?></div></div>
        <div class="stat"><div class="k">Forward P/E</div><div class="v"><?= num($s['forward_pe']) ?></div></div>
        <div class="stat"><div class="k">EPS</div><div class="v"><?= money($s['eps'], $cur) ?></div></div>
        <div class="stat"><div class="k">תשואת דיבידנד</div><div class="v"><?= $s['dividend_yield'] ? num($s['dividend_yield']).'%' : '—' ?></div></div>
        <div class="stat"><div class="k">Beta</div><div class="v"><?= num($s['beta']) ?></div></div>
        <div class="stat"><div class="k">שיא 52ש'</div><div class="v"><?= money($s['week52_high'], $cur) ?></div></div>
        <div class="stat"><div class="k">שפל 52ש'</div><div class="v"><?= money($s['week52_low'], $cur) ?></div></div>
        <div class="stat"><div class="k">נפח</div><div class="v"><?= big_number($s['volume']) ?></div></div>
        <div class="stat"><div class="k">הכנסות</div><div class="v"><?= big_number($s['revenue'], $cur) ?></div></div>
        <div class="stat"><div class="k">רווח נקי</div><div class="v"><?= big_number($s['net_income'], $cur) ?></div></div>
        <div class="stat"><div class="k">עובדים</div><div class="v"><?= int_num($s['employees']) ?></div></div>
      </div>

      <!-- Chart -->
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-graph-up"></i> גרף מחיר (שנה אחרונה)</h2></div>
        <div class="panel-body"><div style="height:320px"><canvas id="priceChart"></canvas></div></div>
      </div>

      <!-- Content sections -->
      <div class="panel section"><div class="panel-body prose">
        <h2 id="overview">סקירה כללית</h2>
        <?= Content::stockOverview($s) ?>

        <h2 id="business">תיאור עסקי ומודל פעילות</h2>
        <?= Content::stockBusiness($s) ?>

        <h2 id="products">מוצרים ושירותים</h2>
        <?= Content::stockProducts($s) ?>

        <h2 id="revenue">מקורות הכנסה</h2>
        <?= Content::stockRevenueSources($s) ?>

        <h2 id="moat">יתרונות תחרותיים</h2>
        <?= Content::stockCompetitiveAdvantages($s) ?>

        <h2 id="growth">מנועי צמיחה</h2>
        <?= Content::stockGrowthDrivers($s) ?>

        <h2 id="risks">גורמי סיכון</h2>
        <?= Content::stockRisks($s) ?>

        <h2 id="valuation">ניתוח תמחור</h2>
        <?= Content::stockValuation($s) ?>

        <h2 id="dividend">ניתוח דיבידנד</h2>
        <?= Content::stockDividend($s) ?>

        <h2 id="performance">ניתוח ביצועים</h2>
        <?= Content::stockPerformance($s) ?>
      </div></div>

      <!-- Financials -->
      <?php if (!empty($financials)): ?>
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-table"></i> נתונים פיננסיים שנתיים</h2></div>
        <div class="panel-body p-0"><div class="table-responsive"><table class="table asset-table mb-0">
          <thead><tr><th>שנה</th><th class="text-start">הכנסות</th><th class="text-start">רווח גולמי</th><th class="text-start">רווח תפעולי</th><th class="text-start">רווח נקי</th><th class="text-start">EPS</th></tr></thead>
          <tbody>
          <?php foreach ($financials as $f): ?>
            <tr>
              <td><strong><?= e($f['fiscal_year']) ?></strong></td>
              <td class="text-start"><?= big_number($f['revenue'], $cur) ?></td>
              <td class="text-start"><?= big_number($f['gross_profit'], $cur) ?></td>
              <td class="text-start"><?= big_number($f['operating_income'], $cur) ?></td>
              <td class="text-start"><?= big_number($f['net_income'], $cur) ?></td>
              <td class="text-start"><?= money($f['eps'], $cur) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div></div>
      </div>
      <?php endif; ?>

      <!-- Competitors -->
      <?php if (!empty($competitors)): ?>
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-people"></i> מתחרים ומניות קשורות</h2></div>
        <div class="panel-body p-0"><?= View::partial('partials/stocks_table', ['rows' => $competitors, 'showDividend' => true]) ?></div>
      </div>
      <?php endif; ?>

      <!-- Related ETFs -->
      <?php if (!empty($etfs)): ?>
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-collection"></i> קרנות סל המחזיקות במניה</h2></div>
        <div class="panel-body">
          <?php foreach ($etfs as $e): ?>
          <a class="tag-pill" href="<?= url('etf/' . e($e['ticker'])) ?>" style="margin:4px;display:inline-block"><?= e($e['ticker']) ?> <?= $e['weight'] ? '· '.num($e['weight']).'%' : '' ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
      <?= View::partial('partials/disclaimer') ?>
    </div>

    <!-- Sidebar TOC -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="toc">
        <h4>תוכן העניינים</h4>
        <a href="#overview">סקירה כללית</a>
        <a href="#business">תיאור עסקי</a>
        <a href="#products">מוצרים ושירותים</a>
        <a href="#revenue">מקורות הכנסה</a>
        <a href="#moat">יתרונות תחרותיים</a>
        <a href="#growth">מנועי צמיחה</a>
        <a href="#risks">גורמי סיכון</a>
        <a href="#valuation">ניתוח תמחור</a>
        <a href="#dividend">ניתוח דיבידנד</a>
        <a href="#performance">ניתוח ביצועים</a>
        <a href="#faq">שאלות נפוצות</a>
      </div>
    </div>
  </div>
</div>

<?php
View::pushScript("window.xbtChart('priceChart', " . json_encode($chartLabels, JSON_UNESCAPED_UNICODE) . ", " . json_encode($chartData) . ", 'מחיר', '#1457e6');");
?>
