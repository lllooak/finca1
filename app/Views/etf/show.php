<?php
use App\Core\View;
use App\Support\Content;
/** @var array $e,$faqs,$holdings,$similar,$alloc */
$name = $e['name_he'] ?: $e['name'];
$cur = $e['currency_code'];
?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero">
  <div class="container">
    <div class="ah-top">
      <div class="asset-logo"><?= e(mb_substr($e['ticker'], 0, 2)) ?></div>
      <div>
        <h1><?= e($name) ?> <span class="text-soft" style="font-size:1.1rem">(<?= e($e['ticker']) ?>)</span></h1>
        <div class="ah-meta"><?= e($e['issuer']) ?> · <?= e($e['asset_class']) ?> · עוקב אחר <?= e($e['benchmark']) ?></div>
      </div>
    </div>
    <div class="asset-price">
      <span class="px"><?= money($e['price'], $cur) ?></span>
      <span class="chg <?= change_class($e['day_change_pct']) ?>"><?= pct($e['day_change_pct']) ?></span>
    </div>
  </div>
</section>

<div class="container py-4">
  <div class="stat-grid mb-4">
    <div class="stat"><div class="k">נכסים מנוהלים</div><div class="v"><?= big_number($e['aum'], $cur) ?></div></div>
    <div class="stat"><div class="k">דמי ניהול</div><div class="v"><?= $e['expense_ratio'] ? num($e['expense_ratio']).'%' : '—' ?></div></div>
    <div class="stat"><div class="k">תשואת דיבידנד</div><div class="v"><?= $e['dividend_yield'] ? num($e['dividend_yield']).'%' : '—' ?></div></div>
    <div class="stat"><div class="k">מספר החזקות</div><div class="v"><?= int_num($e['holdings_count']) ?></div></div>
    <div class="stat"><div class="k">תשואה YTD</div><div class="v <?= change_class($e['ytd_return']) ?>"><?= pct($e['ytd_return']) ?></div></div>
    <div class="stat"><div class="k">תשואה שנה</div><div class="v <?= change_class($e['return_1y']) ?>"><?= pct($e['return_1y']) ?></div></div>
    <div class="stat"><div class="k">תשואה 3ש'</div><div class="v <?= change_class($e['return_3y']) ?>"><?= pct($e['return_3y']) ?></div></div>
    <div class="stat"><div class="k">Beta</div><div class="v"><?= num($e['beta']) ?></div></div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="panel section"><div class="panel-body prose">
        <h2>סקירה כללית</h2>
        <?= Content::etfOverview($e) ?>
      </div></div>

      <?php if (!empty($holdings)): ?>
      <div class="panel section">
        <div class="panel-head"><h2><i class="bi bi-list-ol"></i> החזקות עיקריות</h2></div>
        <div class="panel-body p-0"><div class="table-responsive"><table class="table asset-table mb-0">
          <thead><tr><th>#</th><th>שם</th><th>סימול</th><th class="text-start">משקל</th><th class="text-start d-none d-md-table-cell">סקטור</th></tr></thead>
          <tbody>
          <?php foreach ($holdings as $i => $h): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td class="asset-name"><?php if ($h['stock_id'] && $h['holding_ticker']): ?><a href="<?= url('stock/' . e($h['holding_ticker'])) ?>"><?= e($h['holding_name']) ?></a><?php else: ?><?= e($h['holding_name']) ?><?php endif; ?></td>
              <td class="text-soft"><?= e($h['holding_ticker']) ?></td>
              <td class="text-start"><strong><?= num($h['weight']) ?>%</strong></td>
              <td class="text-start d-none d-md-table-cell text-soft"><?= e($h['sector']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table></div></div>
      </div>
      <?php endif; ?>

      <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
      <?= View::partial('partials/disclaimer') ?>
    </div>

    <div class="col-lg-4">
      <?php if (!empty($alloc)): ?>
      <div class="panel section">
        <div class="panel-head"><h3><i class="bi bi-pie-chart"></i> פיזור סקטוריאלי</h3></div>
        <div class="panel-body"><div style="height:260px"><canvas id="allocChart"></canvas></div></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($similar)): ?>
      <div class="panel section">
        <div class="panel-head"><h3>קרנות דומות</h3></div>
        <div class="panel-body">
          <?php foreach ($similar as $s): ?>
          <a class="d-flex justify-content-between py-2 border-bottom" href="<?= url('etf/' . e($s['ticker'])) ?>">
            <span><strong><?= e($s['ticker']) ?></strong> <span class="text-soft small"><?= e(excerpt($s['name_he'] ?: $s['name'], 28)) ?></span></span>
            <span class="<?= change_class($s['day_change_pct']) ?>"><?= pct($s['day_change_pct']) ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
if (!empty($alloc)) {
    View::pushScript("window.xbtDoughnut('allocChart', " . json_encode(array_keys($alloc), JSON_UNESCAPED_UNICODE) . ", " . json_encode(array_map(fn($v) => round($v, 2), array_values($alloc))) . ");");
}
?>
